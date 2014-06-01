<?php
/**
 * Compose newsletter
 *
 * @package Panthera\core\adminUI\editor_drafts
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
      exit;

$panthera -> locale -> loadDomain('editordrafts');
$panthera -> importModule('editordrafts');

// rights
$management = getUserRightAttribute($panthera->user, 'can_manage_drafts');

// titlebar
$titlebar = new uiTitlebar(localize('Saved drafts', 'editordrafts'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');

if (isset($_GET['id']))
{
    $draft = new editorDraft('id', $_GET['id']);
    
    if (!$draft->exists())
    {
        $panthera -> template -> display('no_page.tpl');
        pa_exit();
    }
    
    /**
      * Save a draft
      *
      * @author Damian Kęska
      */
    
    if ($_GET['action'] == 'saveDraft')
    {
        if (($draft -> author_id != $panthera -> user -> id) and !$management)
        {
            $noAccess = new uiNoAccess;
            $noAccess -> addMetas(array('can_manage_drafts'));
            $noAccess -> display();
        }
    
        // update existing draft when id is provided
        editorDraft::createDraft($_POST['content'], $panthera->user->id, $draft->id);
        ajax_exit(array('status' => 'success'));
        
    /**
      * Remove a draft
      *
      * @author Damian Kęska
      */
        
    } elseif ($_POST['action'] == 'removeDraft') {
    
        // remove draft only if current user is its author or can manage drafts of all users
        if (($draft -> author_id == $panthera -> user -> id) or $management)
        {
            editorDraft::removeDraft($_GET['id']);
            ajax_exit(array('status' => 'success'));
        } else {
            $noAccess = new uiNoAccess;
            $noAccess -> addMetas(array('can_manage_drafts'));
            $noAccess -> display();
        }
        
    } else {
    
        if ($panthera -> user -> id == $draft -> author_id)
        {
            $author = $panthera -> user -> getName();
        } else {
            $u = new pantheraUser('id', $draft->author_id);
            $author = $u->id;
        }
        
        $panthera -> template -> push ('callback', $_GET['callback']);
        $panthera -> template -> push('content', filterInput($draft->content, 'wysiwyg'));
        $panthera -> template -> push('date', $draft->date);
        $panthera -> template -> push('author', $author);
        $panthera -> template -> push('draftID', $draft->id);
        $panthera -> template -> display('drafts_edit.tpl');
        pa_exit();
    }
}

$count = editorDraft::fetchByUser($panthera -> user -> id, '', False, False);

$uiPager = new uiPager('adminEditorDrafts', $count);
$uiPager -> setActive(intval($_GET['page']));
$uiPager -> setLinkTemplatesFromConfig('editor_drafts.tpl');
$limit = $uiPager -> getPageLimit();

$drafts = array();

foreach (editorDraft::fetchByUser($panthera -> user -> id, '', $limit[1], $limit[0]) as $draft)
{
    $draft['user'] = $panthera -> user -> getName();
    $drafts[$draft['id']] = $draft;
}

$panthera -> template -> push ('callback', $_GET['callback']);
$panthera -> template -> push ('drafts', $drafts);
$panthera -> template -> display('editor_drafts.tpl');
pa_exit();
