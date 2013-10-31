<?php
/**
    * @package Panthera
    * @subpackage core
    * @copyright (C) Damian Kęska, Mateusz Warzyński
    * @license GNU Affero General Public License 3, see license.txt
    */

if (!defined('IN_PANTHERA'))
    exit;

// register plugin
$pluginInfo = array('name' => 'Nice comments', 'author' => 'Mateusz Warzyński', 'description' => 'A simple comments system for Panthera Framework', 'version' => PANTHERA_VERSION);
$panthera -> addPermission('can_manage_comments', localize('Can manage all niceComments elements', 'messages'));
$panthera -> addPermission('can_add_comments', localize('Can add niceComment element', 'messages'));


class niceComment extends pantheraFetchDB
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
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}comments` WHERE `id` = :id', array('id' => $value));
            break;

            case 'content_id':
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}comments` WHERE `content_id` = :content_id', array('content_id' => $value));
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
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}comments` WHERE '.$q[0]. ' ORDER BY `id` DESC LIMIT 0,1', $q[1]);
                } else
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}comments` ORDER BY `id`');

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
                $panthera->logging->output('plugin::niceComments::Found a nice comment by ' .$by. ' (value=' .json_encode($value). ')');
        } else {
            if($panthera->logging->debug == True)
                $panthera->logging->output('plugin::niceComments::Cannot find a nice comment by ' .$by. ' (value=' .json_encode($value). ')');
            return false;
        }

        $panthera -> add_option('session_save', array($this, 'save'));
    }

    public function save()
    {
        if($this->_dataModified == True)
        {
            $id = (integer)$this->_data['id'];
            $this->panthera->logging->output('plugin::niceComments::Saving nice comment with id ' .$id);

            // we cant use id, so we have to remove it (id cant be changed because its used in WHERE clause)
            $copied = $this->_data;
            unset($copied['id']);
            unset($copied['mod_time']);

            // $set[0] will be a query string like `id` = :id, `name` = :name and $set[1] will be values array('id' => 1, 'name' => 'Damien')
            $set = $this->panthera->db->dbSet($copied);
            $set[1]['id'] = $id;

            $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}comments` SET ' .$set[0]. ', `mod_time` = NOW() WHERE `id` = :id;', $set[1]);

            // data was already saved, so we are returning to previous state
            $this->_dataModified = False;
        }
    }
}


/**
 * Create nice comment
 *
 * @return bool
 * @author Mateusz Warzyński
 */

function createNiceComment($title, $content, $author_id, $author_login, $content_id, $language)
{
    global $panthera;
    $array = array('title' => $title, 'content' => $content, 'mod_author_id' => $author_id, 'mod_author_login' => $author_login, 'author_id' => $author_id, 'author_login' => $author_login, 'votes_up' => '0', 'votes_down' => '0', 'votes_rank' => '0', 'content_id' => $content_id, 'language' => $language);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}comments` (`id`, `title`, `content`, `date`, `mod_time`, `mod_author_id`, `mod_author_login`, `author_id`, `author_login`, `votes_up`, `votes_down`, `votes_rank`, `content_id`, `language`) VALUES (NULL, :title, :content, NOW(), NOW(), :mod_author_id, :mod_author_login, :author_id, :author_login, :votes_up, :votes_down, :votes_rank, :content_id, :language);', $array);

    if ($SQL)
      return True;

    return False;
}


/**
 * Simply remove nice comment by `id`. Returns True if any row was affected
 *
 * @return bool
 * @author Mateusz Warzyński
 */

function removeNiceComment($id)
{
    global $panthera;
    $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}comments` WHERE `id` = :id', array('id' => $id));

    if ($SQL)
        return True;

    return False;
}

/**
 * Get nice comments from `{$db_prefix}comments` matching criteries specified in parameters
 *
 * @return array
 * @author Mateusz Warzyński
 */

/*    How to get filtered niceComments? There I present you one way to get them:
 * $count = getNiceComments(array('column' => $value), False);
 * $comments = getNiceComments(array('column' => $value), $count, 0);
 */

function getNiceComments($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
{
      global $panthera;
      return $panthera->db->getRows('comments', $by, $limit, $limitFrom, 'niceComment', $orderBy, $order);
}

/*
 * Check content by id
 *
 * @return bool
 * @author Mateusz Warzyński
 */

// function goes here...

/**
 * Display ajax content
 *
 * @return void
 * @author Mateusz Warzyński
 */

function nCommentsAjax()
{
    global $panthera, $user, $template;
    if ($_GET['display'] == 'comments')
    {
        $tpl = 'comments.tpl';

        $panthera -> locale -> loadDomain('comments');

        $displayList = False;

        if (!getUserRightAttribute($user, 'can_manage_comments'))
        {
            ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this site', 'messages')));
            pa_exit();
        }

        /* HTML Pages */

        // edit form of existing comment
        if (@$_GET['action'] == 'edit_comment')
        {
            $tpl = 'comments_edit_comment.tpl';

            $cmtid = intval($_GET['cmtid']);

            $comment = new niceComment('id', $cmtid);

            if ($comment -> exists())
            {
                  $template -> push('id', $comment->id);
                  $template -> push('title', $comment->title);
                  $template -> push('content', $comment->content);
                  $template -> push('content_id', $comment->content_id);
                  $template -> push('action', 'edit_comment');
                  $template -> display($tpl);
            }
            pa_exit();
        }

        // add form of new comment
        /*if (@$_GET['action'] == 'add_comment')
        {
            $content_id = intval($_GET['cntid']);

            $template -> push('content_id', $content_id);
            $template -> push('action', 'add_comment');
            $template -> display($tpl);
            pa_exit();
        }*/

        if (@$_GET['action'] == 'show_comments')
        {
            $tpl = 'comments_list.tpl';
            $content_id = $_GET['cmtid'];

            $c = getNiceComments(array('content_id' => $content_id));

            if (count($c) > 0)
            {
                  foreach ($c as $comment)
                  {
                        $array[] = array('id' => $comment->id, 'title' => $comment->title, 'content' => substr($comment->content, 0, 100), 'modified' => $comment->mod_time, 'date' => $comment->date, 'author_login' => $comment->author_login, 'content_id' => $comment->content_id);
                  }
                  $template->push('comments_list', $array);
            }

            $template -> push('action', 'show_comments');
            $template -> display($tpl);
            pa_exit();
        }

        /* End of HTML pages */


        /* Ajax pages */

        // remove existing comment
        if (@$_GET['action'] == 'delete_comment') {
            if (!getUserRightAttribute($user, 'can_manage_comments'))
            {
                print(json_encode(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages'))));
                pa_exit();
            }

            if (removeNiceComment($_GET['cmtid']))
                ajax_exit(array('status' => 'success', 'message' => localize('Comment has been successfully deleted!')));
            else
                ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error / Cannot remove comment', 'messages')));

            pa_exit();

        }

        // save editing comment
        if (@$_GET['action'] == 'save') {

            if (!getUserRightAttribute($user, 'can_manage_comments')) {
                ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));
            }

            if ($_POST['title'] == '' or $_POST['content'] == '' or $_POST['content_id'] == '')   {
                ajax_exit(array('status' => 'failed', 'message' => 'Some areas are empty!'));
            }

            $comment = new niceComment('id', intval($_POST['id']));

            if ($comment -> exists()) {
                $_POST['title'] = filterInput($_POST['title'], 'quotehtml');
                $_POST['content'] = filterInput($_POST['content'], 'quotehtml');
                $comment -> title = $_POST['title'];
                $comment -> content = $_POST['content'];
                $comment -> mod_author_id = $user -> id;
                $comment -> mod_author_login = $user -> login;
                ajax_exit(array('status' => 'success', 'message' => localize('Comment has been successfully changed!', 'ncomments')));
            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error / Cannot remove comment', 'messages')));
            }
        }

        /* End of Ajax pages */

        // Show main list of messages (with comments)
        $c = getNiceComments(array('language' => $panthera -> locale -> getActive()));

        if (count($c) > 0)
        {
              $array = array();
              foreach ($c as $comment)
              {
                    $id = explode("_", $comment -> content_id);
                    if ($id[0] == 'qmessages')
                        $i = new quickMessage('id', $id[1]);
                    elseif ($id[0] == 'custompage')
                        $i = new customPage('id', $id[1]);
                    /*elseif ($id[0] == 'galleryitem')
                        $i = new galleryItem('id', $id[1]);*/

                    if (!array_key_exists($comment -> content_id, $array))
                        $array[$comment -> content_id] = array('title' => $i -> title, 'author_login' => $i -> author_login, 'author_full_name' => $i -> author_full_name, 'mod_time' => $i -> mod_time);
              }
              $template->push('items_list', $array);
        }

        $template -> display($tpl);
        pa_exit();
    }
}

// Add 'comments' item to admin menu
function commentsToAdminMenu($menu) { $menu -> add('comments', localize('Comments'), '?display=comments', '', '', ''); return $menu;}
$panthera -> add_option('admin_menu', 'commentsToAdminMenu');

$panthera -> add_option('ajax_page', 'nCommentsAjax');
?>
