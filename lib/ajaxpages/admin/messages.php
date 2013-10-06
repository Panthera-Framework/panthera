<?php
/**
  * Configuration tool to change values in config overlay
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

$panthera -> importModule('quickmessages');
$panthera -> locale -> loadDomain('qmessages');

// get active locale with override if avaliable
$language = $panthera -> locale -> getFromOverride($_GET['language']);
$panthera -> template -> push ('language', $language);

// rights
$isAdmin = checkUserPermissions($panthera->user, True); $panthera -> template -> push('isAdmin', $isAdmin);
$canManageAll = getUserRightAttribute($user, 'can_qmsg_manage_all');

/**
  * Creating a new message
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'new_msg')
{
    $_POST = $panthera->get_filters('new_qmsg_post', $_POST);
    $title = filterInput(trim($_POST['message_title']), 'quotehtml');
    $content = $_POST['message_content'];
    $visibility = (bool)$_POST['message_hidden'];
    $categoryName = $_GET['category'];
    $category = new quickCategory('category_name', $categoryName);
    $icon = filterInput($_POST['message_icon'], 'quotehtml');
    
    // check user rights
    if (!getUserRightAttribute($panthera->user, 'can_qmsg_manage_' .$categoryName) and !getUserRightAttribute($panthera->user, 'can_qmsg_manage_all'))
    {
        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Permission denied. You dont have access to this action', 'messages'
        )));
    }
    
    // set other language than active
    if ($_POST['language'] != $language)
    {
        if ($panthera->locale->exists($_POST['language']))
            $language = $_POST['language'];
    }
    
    if (strlen($_POST['url_id']) == 0)
    {
        $url_id = md5(rand(9999, 99999));
    } else {
        $url_id = seoUrl($_POST['url_id']);
        $q = new quickMessage('url_id', $url_id);
        
        if ($q->exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('A message with specified SEO name already exists', 'qmessages')));    
    }

    if(strlen($title) < 4)
        ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short', 'qmessages')));

    if(!$category -> exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Invalid category', 'qmessages')));

    if (strlen($content) < 4)
        ajax_exit(array('status' => 'failed', 'message' => localize('Content is too short', 'qmessages')));

    quickMessage::create($title, $content, $user->login, $user->full_name, $url_id, $language, $categoryName, $visibility, $icon);
    ajax_exit(array('status' => 'success'));
    
/**
  * Create a new category
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'newCategory') {

    // only if user have full permissions to quick messages module
    if (!getUserRightAttribute($user, 'can_qmsg_manage_all'))
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));
    }
    
    if (strlen($_POST['title']) < 3 or strlen($_POST['title']) > 32)
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Category title should be 3 to 32 characters long', 'qmessages')));
    }
    
    $qmsg = quickCategory::create($_POST['title'], $_POST['description'], $_POST['category_name']);
    
    if ($qmsg -> exists())
    {
        ajax_exit(array('status' => 'success'));
    }
    
    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot create new category, maybe there is already another with same id', 'qmessages')));

} elseif ($_GET['action'] == 'deleteCategory') {

    // category can be deleted only if user has full permissions to quick messages module
    if (!getUserRightAttribute($user, 'can_qmsg_manage_all'))
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));
    }
    
    quickCategory::remove($_POST['category_name']);
    ajax_exit(array('status' => 'success'));

/**
  * Editing a message
  *
  * @author Damian Kęska
  */

} elseif (@$_GET['action'] == 'edit_msg')  {
    $title = filterInput(trim($_POST['edit_msg_title']), 'quotehtml');
    $message = $_POST['edit_msg_content'];
    $msgid = intval($_POST['edit_msg_id']);
    $icon = filterInput($_POST['edit_msg_icon'], 'quotehtml');
    $m = new quickMessage('id', $msgid);

    // check user rights
    if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
    {
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));
    }

    // too short message
    if (strlen($message) < 10)
    {
        ajax_exit(array('status' => 'failed', 'error' => localize('Message content is too short!', 'qmessages')));
    }

    // check if message exists
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
            ajax_exit(array('status' => 'failed', 'error' => localize('Content is too short', 'qmessages')));
            
        if ($panthera->locale->exists($_POST['edit_language']))
            $m -> language = $_POST['edit_language'];

        if (isset($_POST['edit_msg_hidden']))
            $m -> visibility = 1;
        else
            $m -> visibility = 0;

        $m -> mod_author_login = $user->login;
        $m -> mod_author_full_name = $user->full_name;
        $m -> url_id = seoUrl($title);
        $m -> save();

        // message found, return in ajax response
        ajax_exit(array('status' => 'success', 'title' => $m->title, 'message' => $m->message, 'id' => $m->id, 'mod_time' => $m->mod_time, 'visibility' => $visibility[$m->visibility]));
    } else {
        // if message does not exists
        ajax_exit(array('status' => 'failed', 'error' => localize('The message no longer exists, maybe it was deleted a moment ago', 'qmessages')));
    }

/**
  * Remove a message
  *
  * @author Damian Kęska
  */

} 

if ($_GET['action'] == 'remove_msg') 
{
    $m = new quickMessage('id', intval($_GET['msgid']));

    if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
    {
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));
    }

    if (quickMessage::remove($_GET['msgid']))
        ajax_exit(array('status' => 'success'));
    else
        ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'messages')));
}

/**
  * Get message details (ajax page)
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'get_msg') 
{
    $msgid = intval($_GET['msgid']);
    $m = new quickMessage('id', $msgid);

    if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$m->category_name) and !getUserRightAttribute($user, 'can_qmsg_manage_all') and !getUserRightAttribute($user, 'can_qmsg_edit_' .$m->id))
    {
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));
    }

    if ($m -> exists())
    {
        ajax_exit(array('status' => 'success', 'title' => $m->title, 'message' => $m->message, 'id' => $m->id, 'visibility' => $m->visibility, 'icon' => $m->icon, 'language' => $m->language, 'url_id' => $m->url_id));
    } else {
        ajax_exit(array('status' => 'failed'));
    }
}

/**
  * Display list of quick messages inside of category
  *
  * @param string name
  * @return mixed 
  * @author Damian Kęska
  */

if ($_GET['action'] == 'display_category')
{
    $page = intval($_GET['page']);
    $categoryName = $_GET['category'];
    $category = new quickCategory('category_name', $categoryName);

    if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$categoryName) and !getUserRightAttribute($user, 'can_qmsg_manage_all'))
    {
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));
    }

    if (!$category->exists())
    {
        $panthera -> template -> display('no_page.tpl');
        pa_exit();
    }
        
    if ($page < 0)
        $page = 0;

    // here we will list all messages on default page (listing)
    $count = quickMessage::getQuickMessages(array('language' => $language, 'category_name' => $categoryName), False);
    
    // count pages
    $pager = new uiPager('adminQuickMessages', $count, 'adminQuickMessages');
    $pager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\');');
    $pager -> maxLinks = 6;
    $limit = $pager -> getPageLimit($page);
    
    // category title and description
    $template -> push('category_title',  $category->title);
    $template -> push('category_description', $category->description);
    $template -> push('category_name', $category->category_name);
    $template -> push('category_id', $categoryName);
    $template -> push('languages', $panthera -> locale -> getLocales());

    // get limited results
    $m = quickMessage::getQuickMessages(array('language' => $language, 'category_name' => $categoryName), $limit[1], $limit[0]);

    if (count($m) > 0 and $m != False)
    {
        $visibility = array(0 => 'Ukryty', 1 => 'Widoczny');
        $i = 0;

        foreach ($m as $message)
        {
            if ($message->visibility and !$page)
            {
                $i++;
            }

            $array[] = array('id' => $message->id, 'title' => $message->title, 'mod_time' => $message->mod_time, 'visibility' => $visibility[$message->visibility], 'author_login' => $message->author_login, 'icon' => $message->icon);
        }

        $template->push('messages_list', $panthera->get_filters('quick_messages_list', $array));
    }
     
    // ajax response
    if ($_GET['type'] == 'ajax')
        ajax_exit(array('status' => 'success', 'response' => $array, 'pager' => $pager->getPages($page), 'page_from' => $limit[0], 'page_to' => $limit[1]));

	$titlebar = new uiTitlebar(localize('Messages category', 'qmessages'). ' - ' .localize($category->title). ' (' .slocalize('only in %s', 'qmessages', $language). ')');
	$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/messages.png', 'left');

    $template -> display('messages_displaycategory.tpl');
    pa_exit();
}
     
/**
  * Display list of categories
  *
  * @author Damian Kęska
  */
     
// ==== DISPLAY ALL QUICK MESSAGES
if (!getUserRightAttribute($user, 'can_view_qmsg'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$template -> push('action', '');

// Search bar
$panthera -> importModule('admin/ui.searchbar');
$sBar = new uiSearchbar('uiTop');
$sBar -> navigate(True);
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=messages&cat=admin');
$sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_see_qmsg_all,can_manage_qmsg_all', localize('Manage permissions'));

$sBar -> addSetting('order', localize('Order by', 'custompages'), 'select', array(
    'category_id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
    'category_name' => array('title' => 'name', 'selected' => ($_GET['order'] == 'category_name')),
    'title' => array('title' => 'title', 'selected' => ($_GET['order'] == 'title'))
));

$sBar -> addSetting('direction', localize('Direction', 'custompages'), 'select', array(
    'ASC' => array('title' => localize('Ascending'), 'selected' => ($_GET['direction'] == 'ASC')),
    'DESC' => array('title' => localize('Descending'), 'selected' => ($_GET['direction'] == 'DESC'))
));

$page = intval($_GET['page']);
$order = 'category_id'; $orderColumns = array('category_id', 'category_name', 'title');
$direction = 'DESC';

$w = new whereClause();
        
if ($_GET['query'])
{
    $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase
    $w -> add( 'AND', 'title', 'LIKE', '%' .$_GET['query']. '%');
    $w -> add( 'OR', 'description', 'LIKE', '%' .$_GET['query']. '%');
}
        
// order by
if (in_array($_GET['order'], $orderColumns))
{
    $order = $_GET['order'];
 }
        
if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
{
    $direction = $_GET['direction'];
}

$total = quickCategory::getCategories($w, False, False, $order, $direction);

// Pager stuff
$panthera -> importModule('admin/ui.pager');
$uiPager = new uiPager('quickMessages', $total, 'quickMessages');
$uiPager -> setActive($page);
$uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\');');
$limit = $uiPager -> getPageLimit();

// get all categories
$categories = quickCategory::getCategories($w, $limit[1], $limit[0], $order, $direction);

$panthera -> template -> push('categories', $categories);

$titlebar = new uiTitlebar(localize('Articles, quick messages, news etc.', 'qmessages'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/messages.png', 'left');

$panthera -> template -> display('messages.tpl');
pa_exit();
