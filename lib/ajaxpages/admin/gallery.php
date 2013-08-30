<?php
/**
  * Gallery ajax pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

// get active locale with override if avaliable
$language = $panthera -> locale -> getFromOverride($_GET['language']);

$panthera -> importModule('simpleImage');
$panthera -> importModule('gallery');

$tpl = 'gallery.tpl';

$panthera -> locale -> loadDomain('gallery');

error_reporting(E_ALL);

if (!getUserRightAttribute($user, 'can_view_galleryItem'))
{
    $panthera -> template -> display('no_access.tpl');
    pa_exit();
}

if ($_GET['action'] == 'saveCategoryDetails')
{
    $gallery = new galleryCategory('id', intval($_GET['id']));

    if (!$gallery -> exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('There is no such category', 'gallery')));

    if ($panthera->locale->exists($_POST['language']))
        $gallery -> language = $_POST['language'];

    if (strlen($_POST['title']) > 0)
        $gallery -> title = $_POST['title'];

    if (isset($_POST['all_langs']))
        $gallery->meta('unique')->set('all_langs', $gallery->id);
    else
        $gallery->meta('unique')->set('all_langs', False);

    $gallery->meta('unique')->save();
    $gallery -> save(); // just to be sure

    ajax_exit(array('status' => 'success', 'language' => $gallery -> language, 'unique' => $gallery -> unique));
}

/**
  * Add selected uploads to gallery
  *
  * @input json {1, 2, 3, 4, 10, 50, 60} - upload id's
  * @author Damian Kęska
  */

if ($_GET['action'] == 'adduploads')
{
    // here are all files-related functions
    $panthera -> importModule('filesystem');

    // see @input
    $files = json_decode($_POST['ids']);
    $added = 0;

    foreach ($files as $id)
    {
        // get uploaded file
        $file = new uploadedFile('id', $id);

        // validate
        if ($file->exists())
        {
            // add to gallery
            createGalleryItem(basename($file->location), $file->description, pantheraUrl($file->getLink(), True), intval($_GET['gid']), True, $file);
            $added++;
        }
    }

    // return success and simple result
    if ($added > 0)
        ajax_exit(array('status' => 'success', 'count' => $added));

    ajax_exit(array('status' => 'failed'));
}

/**
  * Delete an item from gallery
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'delete_item')
{
    $id = intval($_GET['image_id']);

    // check user rights
    if (!getUserRightAttribute($user, 'can_edit_galleryItem') and !getUserRightAttribute($user, 'gallery_manage_img_' .$id))
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));

    if (gallery::removeImage($id))
        ajax_exit(array('status' => 'success'));
    else
        ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'messages')));
}

/**
  * Remove category from gallery
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'delete_category')
{
    $id = intval($_GET['id']);

    // check user rights
    if (!getUserRightAttribute($user, 'can_edit_galleryItem') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));

    if (removeGalleryCategory($id))
        ajax_exit(array('status' => 'success'));
    else
        ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'messages')));
}

/**
  * Toggle gallery visibility
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'toggle_gallery_visibility')
{
    if (!isset($_GET['ctgid']))
        pa_exit();

    $id = intval($_GET['ctgid']);
    $item = new galleryCategory('id', $id);

    if ($item -> exists())
    {
        $item -> visibility = !(bool)$item->visibility;
        ajax_exit(array('status' => 'success', 'visible' => $item->visibility));
    } else
        ajax_exit(array('status' => 'failed', 'error' => localize('Category does not exists')));
}

/**
  * Toggle image visibility
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if (@$_GET['action'] == 'toggle_item_visibility')
{
    if (!isset($_GET['itid']))
        pa_exit();

    $id = intval($_GET['itid']);

    $item = new galleryItem('id', $id);

    if ($item -> exists())
    {
        $item -> visibility = !(bool)$item->visibility;
        ajax_exit(array('status' => 'success', 'visible' => $item -> visibility));
    } else
        ajax_exit(array('status' => 'failed', 'error' => localize('Item does not exists')));
}

/**
  * Creating a new category
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'create_category')
{
    // check if user can edit gallery items
    if (!getUserRightAttribute($user, 'can_edit_galleryItem'))
        ajax_exit(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages')));

    if ($_POST['title'] != '')
    {
        if (isset($_POST['visibility']))
        {
            if (createGalleryCategory($_POST['title'], $user->login, $user->id, $user->language, intval($_POST['visibility']), $user->full_name))
                ajax_exit(array('status' => 'success'));
            else
                ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'gallery')));
        }
    }
}

/**
  * Display list with gallery categories
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'display_category')
{
    if (!isset($_GET['unique']))
        pa_exit();

    $template -> push('action', 'display_category');

    // query for a page using `unique` and `language` columns
    $statement = new whereClause();
    $statement -> add('', 'unique', '=', $_GET['unique']);
    $statement -> add('AND', 'language', '=', $language);
    $category = new galleryCategory($statement, null);

    if (!$category->exists())
    {
        $ctg = new galleryCategory('unique', $_GET['unique']);

        if ($ctg -> exists())
        {
            if ($ctg->meta('unique')->get('all_langs') != intval($category->id))
            {
                $newID = $ctg->meta('unique')->get('all_langs');
                $category = new galleryCategory('id', $newID);
                unset($ctg);
            } else {
                // create a category in a new language

                gallery::createCategory($ctg->title, $panthera->user->login, $panthera->user->id, $language, 0, $panthera->user->full_name, $ctg->unique);
                $statement = new whereClause();
                $statement -> add('', 'unique', '=', $_GET['unique']);
                $statement -> add('AND', 'language', '=', $language);
                $category = new galleryCategory($statement, null);
                $category -> thumb_url = $ctg->thumb_url;
                $category -> thumb_id = $ctg->thumb_id;
                unset($ctg);
            }
        }
    }

    // check language
    if (intval($category->meta('unique')->get('all_langs')) > 0)
    {
        if (intval($category->meta('unique')->get('all_langs')) != intval($category->id))
        {
            // load other category which is marked as for all languages
            $ctg = new galleryCategory('id', $category->meta('unique')->get('all_langs'));

            // replace only if new category exists
            if ($ctg->exists())
                $category = $ctg;
         }
    }

    // get gallery items
    $count = getGalleryItems(array('gallery_id' => $category->id), False);
    $i = getGalleryItems(array('gallery_id' => $category->id), $count, 0);

    $template -> push('category_title', $category->title);
    $template -> push('category_id', $category->id);
    $template -> push('item_list', $i);
    $template -> push('langauge', $category->language);
    $template -> push('unique', $_GET['unique']);
    $template -> push('languages', $panthera->locale->getLocales());
    $template -> push('galleryObject', $category);

    if (intval($category->meta('unique')->get('all_langs')) > 0)
        $template -> push('all_langs', True);

    // get custom styles for gallery in both languages and for gallery in single language
    $header = $category->meta('unique')->get('site_header');

    if ($category->meta('id')->get('site_header') != null)
        $header = array_merge($header, $category->meta('unique')->get('site_header'));

    //$header = unserialize('a:2:{s:7:"scripts";a:0:{}s:6:"styles";a:1:{i:0;s:49:"{$PANTHERA_URL}/css/admin/gallery_no_settings.css";}}');
    //$category->meta('unique')->set('site_header', $header);
    //$category->meta('unique')->save();

    // add custom styles and scripts
    if (count($header) > 0)
    {
        if (count($header['scripts']) > 0)
        {
            foreach ($header['scripts'] as $key => $value)
                $panthera -> template -> addScript($value);
        }

        if (count($header['styles']) > 0)
        {
            foreach ($header['styles'] as $key => $value)
                $panthera -> template -> addStyle($value);
        }
    }

    /*$count = getGalleryCategories(array('language' => $user->language), False);
    $c = getGalleryCategories(array('language' => $user->language), $count, 0);

    $template -> push('category_list', $c);*/
    
    $titlebar = new uiTitlebar($category->title . " (". $category->language.")");
	$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/gallery.png', 'left');

    $template -> display('gallery_displaycategory.tpl');
    pa_exit();
}

/**
  * New item form
  *
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'edit_item_form')
{
    $panthera -> importModule('filesystem');
    $template -> push('action', 'edit_item');

    if ($_GET['subaction'] == 'edit_item')
    {
        $id = intval($_GET['id']);
        $item = new galleryItem('id', $id);
        $_POST['upload_id'] = intval($_POST['upload_id']);

        if ($item -> exists())
        {
            $file = new uploadedFile('id', $_POST['upload_id']);

            if (!$file -> exists())
                ajax_exit(array('status' => 'failed', 'message' => localize('Selected file doesnt exists in upload list', 'gallery')));

            if ($_POST['visibility'] == '1')
                $item -> visibility = 0;
            else
                $item -> visibility = 1;

            $item -> title = filterInput($_POST['title'], 'quotehtml');
            $item -> description = filterInput($_POST['description'], 'quotehtml');
            $item -> link = pantheraUrl($file->getLink());
            $item -> thumbnail = $file->getThumbnail($panthera->config->getKey('gallery_thumbs_width', 240, 'int'), True);
            $item -> upload_id = $_POST['upload_id'];

            $category = new galleryCategory('id', $_POST['gallery_id']);

            if ($category -> exists())
                $item -> gallery_id = $_POST['gallery_id'];

            ajax_exit(array('status' => 'success', 'unique' => $item->unique));
        } else
            ajax_exit(array('status' => 'failed', 'error' => localize('Error with changing item!')));
    }

    $id = intval($_GET['itid']);
    $item = new galleryItem('id', $id);

    if ($item -> exists())
    {
        $template -> push('id', $item -> id);
        $template -> push('title', $item -> title);
        $template -> push('description', $item -> description);
        $template -> push('link', pantheraUrl($item -> link));
        $template -> push('thumbnail', pantheraUrl($item -> thumbnail)); // We haven't script to make thumbnails yet. Later we'll delete this line. /M.
        $template -> push('gallery_id', $item -> gallery_id);
        $template -> push('visibility', !$item -> visibility);
        $template -> push('upload_id', $item -> upload_id);

        $c = getGalleryCategories('');
        $template -> push('category_list', $c);

        $category = new galleryCategory('id', $item->gallery_id);
        $template -> push('unique', $category->unique);
        $template -> push('language', $category->language);

		$titlebar = new uiTitlebar(localize('Editing gallery image', 'gallery'));
		$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/gallery.png', 'left');
		
        $template -> display('gallery_edititem.tpl');
        pa_exit();
    } else {
        pa_exit();
    }
}

    if (@$_GET['action'] == 'add_item') {

        if (!getUserRightAttribute($user, 'gallery_manage_img') and !getUserRightAttribute($user, 'gallery_manage_img_' .$id))
        {
              print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
              pa_exit();
        }

        if ($_GET['subaction'] == 'add') {

            $panthera -> importModule('filesystem');

            if ($_POST['title'] != '' and $_POST['gallery_id'] != '' and $_POST['upload_id'] != '')   {

        if ($_POST['visibility'] == '1')
              $visibility = 1;
        else
              $visibility = 0;

        $_POST['title'] = filterInput($_POST['title'], 'quotehtml');
        $_POST['description'] = filterInput($_POST['description'], 'quotehtml');
        $uploadID = intval($_POST['upload_id']);
        $file = new uploadedFile('id', $uploadID);

        if (!$file -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Selected file doesnt exists in upload list', 'gallery')));

        $link = pantheraUrl($file->getLink(), True);

        if (createGalleryItem($_POST['title'], $_POST['description'], $link, intval($_POST['gallery_id']), $visibility, $file))
              ajax_exit(array('status' => 'success', 'ctgid' => $_POST['gallery_id']));
        else
              ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error', 'messages')));

            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Please fill all form inputs', 'gallery')));
            }

            pa_exit();
        }

        $template -> push('action', 'add_item');
        $c = getGalleryCategories('');

        $category = new galleryCategory('id', $_GET['ctgid']);

        if ($category -> exists())
        {
            $template -> push('category_list', $c);
            $template -> push('category_id', $_GET['ctgid']);
            $template -> push('gallery_name', $category->title);
            $template -> push('unique', $category->unique);
            $template -> push('language', $category->language);
			
			$titlebar = new uiTitlebar(localize('Adding gallery image', 'gallery'));
			$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/gallery.png', 'left');
			
            $template -> display('gallery_additem.tpl');
            pa_exit();
        }

    }

    /**
      * Creating new category
      *
      * @author Mateusz Warzyński
      */

    if ($_GET['action'] == 'add_category')
    {
        // check user rights
        if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
        {
            print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
            pa_exit();
        }

        if ($_GET['new_title'] != '')
        {
            gallery::createCategory($_GET['filter'].$_GET['new_title'], $user->login, $user->id, $user->language, intval($_GET['visibility']), $user->full_name, md5(rand(999, 9999)));
            print(json_encode(array('status' => 'success')));
        } else {
            print(json_encode(array('status' => 'failed', 'error' => localize('Title cannot be empty', 'gallery'))));
        }

        pa_exit();
    }

    /**
      * Setting gallery thumbnail from gallery image
      *
      * @author Mateusz Warzyński
      */

    if ($_GET['action'] == 'set_category_thumb')
    {
        if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
        {
              print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'gallery'))));
              pa_exit();
        }

        $id = intval($_GET['itid']);
        $ctgid = intval($_GET['ctgid']);

        $item = new galleryItem('id', $id);
        $category = new galleryCategory('id', $ctgid);

        if ($item -> exists() and $category -> exists())
        {
             $category -> thumb_id = $item -> id;
             $category -> thumb_url = $item -> link;
             print(json_encode(array('status' => 'success')));
        } else {
             print(json_encode(array('status' => 'failed', 'error' => localize('Error with changing gallery thumbnail!', 'gallery'))));
              pa_exit();
        }
        pa_exit();
    }

    /**
      * Editing category title and visibility
      *
      * @author Mateusz Warzyński
      */

    if ($_GET['action'] == 'edit_category')
    {

        if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
        {
              print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'gallery'))));
              pa_exit();
        }

        $id = intval($_GET['ctgid']);

        $item = new galleryCategory('id', $id);

        if ($item -> exists())
        {
            $response = array('status' => 'success');

            if (isset($_GET['new_title']) and $_GET['new_title'] != '') {
                $item -> title = filterInput($_GET['new_title'], 'quotehtml');
                $response['title'] = filterInput($_GET['new_title'], 'quotehtml');
            } else {
                print(json_encode(array('status' => 'failed', 'error' => localize("Title can't be empty", 'gallery'))));
                pa_exit();
            }

            if (isset($_GET['visibility']))
            {

                if ($_GET['visibility'] == 'show')
                {
                    $item -> visibility = True;
                    $response['visibility'] = 'show';
                }

                if ($_GET['visibility'] == 'hide')
                {
                    $item -> visibility = False;
                    $response['visibility'] = 'hide';
                }
            }

            ajax_exit($response);

        } else {
              ajax_exit(array('status' => 'failed', 'error' => localize('Category does not exists')));
              pa_exit();
        }
        pa_exit();
    }

    /*$conditions = '';

    if (isset($_GET['language']))
    {
        if ($_GET['language'] == '' or $_GET['language'] == 'all')
            $panthera -> session -> set('admin_gallery_locale', '');
        elseif (array_key_exists($_GET['language'], $panthera -> locale -> getLocales()))
            $panthera -> session -> set('admin_gallery_locale', $_GET['language']);
    }

    if ($panthera->session->exists('admin_gallery_locale'))
    {
        if ($panthera -> session -> get('admin_gallery_locale') != '')
            $conditions = array('language' => $panthera -> session -> get('admin_gallery_locale'));
    }*/

    // get categories
    $conditions = array('language' => $language);
    $categories = getGalleryCategories($conditions);

    // with title filter
    $categoriesFiltered = array();

    foreach ($categories as $category)
    {
        if ($_GET['filter'] != '')
        {
            if (!stristr($category->title, $_GET['filter']))
                continue;
        }

        $categoriesFiltered[$category->unique] = $category;
    }

    if (defined('GALLERY_FILTER'))
    {
        $template -> push('category_filter', $_GET['filter'].GALLERY_FILTER);
        $template -> push('category_filter_complete', $_GET['filter'].GALLERY_FILTER);
    } else
        $template -> push('category_filter', $_GET['filter']);

    $template -> push('category_list', $categoriesFiltered);
	
	$titlebar = new uiTitlebar(localize('Gallery'));
	$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/gallery.png', 'left');
	
    $template -> display($tpl);
    pa_exit();
