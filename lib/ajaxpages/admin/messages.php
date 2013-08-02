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
    $categoryName = $_GET['cat'];
    $category = new quickCategory('category_name', $categoryName);
    $icon = filterInput($_POST['message_icon'], 'quotehtml');
    
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

    // check user rights
    if (!getUserRightAttribute($user, 'can_qmsg_manage_' .$categoryName) and !getUserRightAttribute($user, 'can_qmsg_manage_all'))
    {
        print(json_encode(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages'))));
        pa_exit();
    }

    if(strlen($title) < 4)
        ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short', 'qmessages')));

    if(!$category -> exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Invalid category', 'qmessages')));

    if (strlen($content) < 4)
        ajax_exit(array('status' => 'failed', 'message' => localize('Content is too short', 'qmessages')));

    createQuickMessage($title, $content, $user->login, $user->full_name, $url_id, $language, $categoryName, $visibility, $icon);
    ajax_exit(array('status' => 'success'));
}

/**
  * Editing a message
  *
  * @author Damian Kęska
  */

if (@$_GET['action'] == 'edit_msg') 
{
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

    if (removeQuickMessage($_GET['msgid']))
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
    $categoryName = $_GET['cat'];

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
    $count = getQuickMessages(array('language' => $language, 'category_name' => $categoryName), False);

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
    $template -> push('category_id', $categoryName);
    $template -> push('languages', $panthera -> locale -> getLocales());

    // special items
    $specialItemsMax = intval($panthera->config->getKey('qmsg_special_count', 4, 'int'));

    // get limited results
    $m = getQuickMessages(array('language' => $language, 'category_name' => $categoryName), $limit[1], $limit[0]);

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
     
    // ajax response
    if ($_GET['type'] == 'ajax')
        ajax_exit(array('status' => 'success', 'response' => $array, 'pager' => $pager->getPages($page), 'page_from' => $limit[0], 'page_to' => $limit[1]));

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
    $template->display('no_access.tpl');
    pa_exit();
}

$template -> push('action', '');

// get all categories
$categories = getQuickCategories('');
$template -> push('categories', $categories);
$template -> display('messages.tpl');
pa_exit();
