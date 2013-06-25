<?php
/**
    * @package Panthera
    * @subpackage core
    * @copyright (C) Damian Kęska, Mateusz Warzyński
    * @license GNU Affero General Public License 3, see license.txt
    */

// register plugin
$pluginInfo = array('name' => 'Quick Messages', 'author' => 'Damian Kęska, Mateusz Warzyński', 'description' => 'News, articles, short messages', 'version' => PANTHERA_VERSION);

$panthera -> addPermission('can_view_qmsg', localize('Can view quick messages', 'messages'));
$panthera -> addPermission('can_qmsg_manage_all', localize('Can manage all quickMessages elements', 'messages'));


class quickMessage extends pantheraFetchDB
{
    protected $panthera;

    public function __construct($by, $value)
    {
        global $panthera;
        $this->panthera = $panthera;

        switch ($by)
        {
            // searching by id
            case 'id':
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}quick_messages` WHERE `id` = :id', array('id' => $value));
            break;

            case 'url_id':
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}quick_messages` WHERE `url_id` = :url_id', array('url_id' => $value));
            break;

            case 'last_result':
                $w = new whereClause();

                if (is_array($value))
                {
                    foreach ($value as $k => $v)
                    {
                        $w -> add( 'AND', $k, '=', $v);
                    }

                    $q = $w -> show();
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}quick_messages` WHERE '.$q[0]. ' ORDER BY `id` DESC LIMIT 0,1', $q[1]);
                } else
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}quick_messages` ORDER BY `id`');

            break;

            // loading data from array (not from SQL query)
            case 'array':
                $this->_data = $value;
                $panthera -> add_option('session_save', array($this, 'save'));
                return True;
            break;

        }

        if ($SQL -> rowCount() > 0)
        {
            $this->_data = $SQL -> fetch();
            if($panthera->logging->debug == True)
                $panthera->logging->output('plugin::quickMessages::Found a quick message by ' .$by. ' (value=' .json_encode($value). ')');
        } else {
            if($panthera->logging->debug == True)
                $panthera->logging->output('plugin::quickMessages::Cannot find a quick message by ' .$by. ' (value=' .json_encode($value). ')');
            return false;
        }

        $panthera -> add_option('session_save', array($this, 'save'));
    }

    public function save()
    {
        if($this->_dataModified == True)
        {
            $id = (integer)$this->_data['id'];
            $this->panthera->logging->output('plugin::quickMessages::Saving quick message with id ' .$id);

            // we cant use id, so we have to remove it (id cant be changed because its used in WHERE clause)
            $copied = $this->_data;
            unset($copied['id']);
            unset($copied['mod_time']);

            // $set[0] will be a query string like `id` = :id, `name` = :name and $set[1] will be values array('id' => 1, 'name' => 'Damien')
            $set = $this->panthera->db->dbSet($copied);
            $set[1]['id'] = $id;

            $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}quick_messages` SET ' .$set[0]. ', `mod_time` = NOW() WHERE `id` = :id;', $set[1]);

            // data was already saved, so we are returning to previous state
            $this->_dataModified = False;
        }
    }
}


/**
 * Create quick message
 *
 * @return void
 * @author Mateusz Warzyński
 */

function createQuickMessage($title, $content, $login, $full_name, $url_id, $language, $category, $visibility=0, $icon='')
{
    global $panthera;
    $array = array('unique' => md5(rand(1,500).$title), 'title' => $title, 'message' => $content, 'author_login' => $login, 'author_full_name' => $full_name, 'visibility' => $visibility, 'mod_author_login' => $login, 'mod_author_full_name' => $full_name, 'url_id' => $url_id, 'language' => $language, 'category_name' => $category, 'icon' => $icon);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}quick_messages` (`id`, `unique`, `title`, `message`, `author_login`, `author_full_name`, `mod_time`, `visibility`, `mod_author_login`, `mod_author_full_name`, `url_id`, `language`, `category_name`, `icon`) VALUES (NULL, :unique, :title, :message, :author_login, :author_full_name, NOW(), :visibility, :mod_author_login, :mod_author_full_name, :url_id, :language, :category_name, :icon);', $array);
}


/**
 * Simply remove quick message by `id`. Returns True if any row was affected
 *
 * @return bool
 * @author Damian Kęska
 */

function removeQuickMessage($id)
{
    global $panthera;
    $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}quick_messages` WHERE `id` = :id', array('id' => $id));

    if ($SQL)
        return True;

    return False;
}

class quickCategory extends pantheraFetchDB
{
    protected $_tableName = 'qmsg_categories';
    protected $_idColumn = 'category_id';
    protected $_constructBy = array('category_id', 'id', 'category_name', 'array'); // `id` because its a synonym to `category_id` - see __construct of pantheraFetchDB
}

/**
 * Get all quick messages from `{$db_prefix}_quick_messages` matching criteries specified in parameters
 *
 * @return array
 * @author Damian Kęska
 */

function getQuickMessages($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
{
      global $panthera;
      return $panthera->db->getRows('quick_messages', $by, $limit, $limitFrom, 'quickMessage', $orderBy, $order);
}

/**
 * Get all categories of quick messages from `{$db_prefix}_qmsg_categories` matching criteries specified in parameters
 *
 * @return array
 * @author Damian Kęska
 */

function getQuickCategories($by, $limit=0, $limitFrom=0, $orderBy='category_id', $order='DESC')
{
      global $panthera;
      return $panthera->db->getRows('qmsg_categories', $by, $limit, $limitFrom, '', $orderBy, $order);
}

/**
 * Display ajax content
 *
 * @return void
 * @author Damian Kęska
 */

function qMessagesAjax()
{
    global $panthera, $user, $template;
    if ($_GET['display'] == 'messages')
    {
        $panthera -> locale -> loadDomain('qmessages');

        $tpl = 'messages.tpl';

        $displayList = False;

        // post new message form
        if (@$_GET['action'] == 'new_msg')
        {
            $_POST = $panthera->get_filters('new_qmsg_post', $_POST);
            $title = filterInput(trim($_POST['message_title']), 'quotehtml');
            $content = $_POST['message_content'];
            $visibility = (bool)$_POST['message_hidden'];
            $categoryName = $_GET['cat'];
            $category = new quickCategory('category_name', $categoryName);
            $icon = filterInput($_POST['message_icon'], 'quotehtml');

            if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$categoryName) and !getUserRightAttribute($user, 'can_qmsg_manage_all'))
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                pa_exit();
            }

            if(strlen($title) < 4)
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Title is too short'))));
                pa_exit();
            }

            if(!$category -> exists())
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Invalid category'))));
                pa_exit();
            }

            if (strlen($content) < 4)
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Content is too short'))));
                pa_exit();
            }

            print(json_encode(array('status' => 'success')));
            createQuickMessage($title, $content, $user->login, $user->full_name, seoUrl($title), $panthera -> locale -> getActive(), $categoryName, $visibility, $icon);
            pa_exit();

        } elseif (@$_GET['action'] == 'edit_msg') {
            $title = filterInput(trim($_POST['edit_msg_title']), 'quotehtml');
            $message = $_POST['edit_msg_content'];
            $msgid = intval($_POST['edit_msg_id']);
            $icon = filterInput($_POST['edit_msg_icon'], 'quotehtml');
            $m = new quickMessage('id', $msgid);

            if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                pa_exit();
            }

            if (strlen($message) < 10)
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Message content is too short!', 'messages'))));
                pa_exit();
            }


            if ($m -> exists())
            {
                $visibility = array(0 => 'Ukryty', 1 => 'Widoczny');

                if (strlen($icon) > 4)
                    $m -> icon = $icon;

                if (strlen($title) > 3)
                    $m -> title = $title;

                if (strlen(strip_tags($title)) > 5)
                    $m -> message = $panthera->get_filters('quick_message', $message);
                else
                    print(json_encode(array('status' => 'failed', 'error' => localize('Content is too short'))));

                if (isset($_POST['edit_msg_hidden']))
                    $m -> visibility = 1;
                else
                    $m -> visibility = 0;

                $m -> mod_author_login = $user->login;
                $m -> mod_author_full_name = $user->full_name;
                $m -> url_id = seoUrl($title);

                print(json_encode(array('status' => 'success', 'title' => $m->title, 'message' => $m->message, 'id' => $m->id, 'mod_time' => $m->mod_time, 'visibility' => $visibility[$m->visibility])));
            } else {
                print(json_encode(array('status' => 'failed', 'error' => localize('The message no longer exists, maybe it was deleted a moment ago'))));
            }

            pa_exit();

        } elseif (@$_GET['action'] == 'remove_msg') {
            $m = new quickMessage('id', intval($_GET['msgid']));

            if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                pa_exit();
            }

            if (removeQuickMessage($_GET['msgid']))
                print(json_encode(array('status' => 'success')));
            else
                print(json_encode(array('status' => 'failed', 'error' => localize('Unknown error', 'messages'))));

            pa_exit();
        } elseif (@$_GET['action'] == 'get_msg') {
            $msgid = intval($_GET['msgid']);

            $m = new quickMessage('id', $msgid);

            if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
            {
                print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                pa_exit();
            }

            if ($m -> exists())
            {
                print(json_encode(array('status' => 'success', 'title' => $m->title, 'message' => $m->message, 'id' => $m->id, 'visibility' => $m->visibility, 'icon' => $m->icon)));

            } else {
                print(json_encode(array('status' => 'failed')));
            }

            pa_exit();

        } else {
            $displayList = True;
        }

        if (@$_GET['action'] == 'display_category' OR $_GET['action'] == 'display_list')
        {
            if ($displayList == True)
            {
                $page = (intval(@$_GET['page']));
                $categoryName = $_GET['cat'];

                $category = new quickCategory('category_name', $categoryName);

                if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$categoryName) and !getUserRightAttribute($user, 'can_qmsg_manage_all'))
                {
                    print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                    pa_exit();
                }

                if (!$category->exists())
                {
                    $template -> display('no_page.tpl');
                    pa_exit();
                }

                if ($page < 0)
                    $page = 0;

                // here we will list all messages on default page (listing)
                $count = getQuickMessages(array('language' => $panthera->locale->getActive(), 'category_name' => $categoryName), False);

                // count pages
                $pager = new Pager($count, $panthera->config->getKey('max_qmsg', 10, 'int'));
                $pager -> maxLinks = 6;
                $limit = $pager -> getPageLimit($page);

                // pager display
                $template -> push('pager', $pager->getPages($page));
                $template -> push('page_from', $limit[0]);
                $template -> push('page_to', $limit[1]);

                // category title and description
                $template -> push('category_title',  $category->title);
                $template -> push('category_description', $category->description);
                $template -> push('category_name', $category->category_name);

                // special items
                $specialItemsMax = intval($panthera->config->getKey('qmsg_special_count', 4, 'int'));

                // get limited results
                $m = getQuickMessages(array('language' => $panthera -> locale -> getActive(), 'category_name' => $categoryName), $limit[1], $limit[0]);

                if (count($m) > 0 and $m != False)
                {
                    $visibility = array(0 => 'Ukryty', 1 => 'Widoczny');
                    $i = 0;

                    foreach ($m as $message)
                    {
                        $special = False;

                        if ($message->visibility == 1 and $page == 0)
                        {
                            $i++;

                            if ($i < $specialItemsMax)
                                $special = True;
                        }

                        $array[] = array('id' => $message->id, 'title' => $message->title, 'mod_time' => $message->mod_time, 'visibility' => $visibility[$message->visibility], 'author_login' => $message->author_login, 'special' => $special, 'icon' => $message->icon);
                    }

                    $template->push('messages_list', $panthera->get_filters('quick_messages_list', $array));
                }

                $tpl = 'messages_displaycategory.tpl';
            }
        } else {
            // ==== DISPLAY ALL QUICK MESSAGES

            if (!getUserRightAttribute($user, 'can_view_qmsg'))
            {
                $template->display('no_access.tpl');
                pa_exit();
            }

            $template -> push('action', '');

            // get all categories
            $categories = getQuickCategories('');
            $template -> push('categories', $categories);
        }


        $template -> display($tpl);
        pa_exit();
    }
}

/**
 * A hooked function, puts link to quickMessages plugin main page to the index of ajax pages
 *
 * @return array
 * @author Damian Kęska
 */

function messagesToAjaxList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'quickMessages', 'link' => '?display=messages');
    return $list;
}

$panthera -> add_option('ajaxpages_list', 'messagesToAjaxList');
$panthera -> add_option('ajax_page', 'qMessagesAjax');

function qmsgToAdminMenu($menu) { $menu -> add('qmessages', localize('Quick messages'), '?display=messages', '', '', ''); }
$panthera -> add_option('admin_menu', 'qmsgToAdminMenu');

function qmsgToDash($attr) { if ($attr[1] != "main") { return $attr; } $attr[0][] = array('link' => '?display=messages', 'name' => localize('Quick messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/messages.png', 'linkType' => 'ajax'); return $attr; }
$panthera -> add_option('dash_menu', 'qmsgToDash');

//$m = getQuickMessages(array('language' => 'polski'), 1);

//foreach ($m as $key => $value)
//{
//    echo $value->title;
//}
#$m = new quickMessage('last_result', array('language' => 'polski'));
#echo $m -> title;
//$m -> title = "Witaj na stronie głównej!";
//$m -> save();
//var_dump($m->title);
?>
