<?php
/**
    * @package Panthera
    * @subpackage core
    * @copyright (C) Damian Kęska, Mateusz Warzyński
    * @license GNU Affero General Public License 3, see license.txt
    */

// register plugin
$pluginInfo = array('name' => 'Gallery', 'author' => 'Mateusz Warzyński, Damian Kęska', 'description' => 'Gallery management with categories', 'version' => PANTHERA_VERSION);
$panthera -> addPermission('can_view_galleryItem', localize('Can view gallery items', 'messages'));
$panthera -> addPermission('can_edit_galleryItem', localize('Can edit gallery items', 'messages'));

class galleryItem extends pantheraFetchDB
{
    protected $_tableName = 'gallery_items';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'link', 'array');

    public function __get($var)
    {
        return pantheraUrl(parent::__get($var));
    }

    public function __set($var, $value)
    {
        return parent::__set($var, pantheraUrl($value, True));
    }

    public function getThumbnail($size='', $create=False)
    {
        $this -> panthera -> importModule('simpleImage');
        $this -> panthera -> importModule('filesystem');


        $link = pantheraUrl($this->__get('link'));
        $filePath = pantheraUrl(str_replace('{$PANTHERA_URL}/', '', pantheraUrl($link, True)));
        $mime = getFileMimeType($filePath);

        // TODO: handling external links (saving to tmp and generating thumbnails)
        if (substr($filePath, 0, 7) == 'http://')
            return False;

        $fileType = fileTypeByMime($mime);
        $fileInfo = pathinfo($filePath);

        if ($size != '')
        {
            $thumb = pantheraUrl('{$upload_dir}/_thumbnails/' .$size. 'px_' .$fileInfo['filename']. '.jpg');

            if(is_file($thumb))
                return $thumb;

            if(!is_file($thumb) and $fileType == 'image' and $create == True)
            {
                $exp = explode('x', $size);

                $simpleImage = new SimpleImage();
                $simpleImage -> load(pantheraUrl($filePath));

                if (count($exp) > 1)
                    $simpleImage -> resize($exp[0], $exp[1]); // resize to WIDTHxHEIGHT
                else
                    $simpleImage -> resizeToWidth($size); // resize to width

                $simpleImage -> save($thumb, IMAGETYPE_JPEG, 85);

                if(is_file($thumb))
                    return $thumb;
            }
        }

        if (is_file(PANTHERA_DIR. '/images/mimes/' .$fileType. '.png'))
            return $url. '/images/mimes/' .$fileType. '.png';

        return $url. '/images/mimes/unknown.png';
    }
}

class galleryCategory extends pantheraFetchDB
{
    protected $_tableName = 'gallery_categories';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
}

class gallery
{
    /**
      * Get most recent picture from gallery
      *
      * @param int $gallery_id Gallery id (optional)
      * @param int $count Count
      * @return object
      * @author Damian Kęska
      */

    public static function getRecentPicture($gallery_id='', $count=1)
    {
        global $panthera;

        $count = intval($count);

        if ($gallery_id != '')
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}gallery_items` WHERE `gallery_id` = :gallery_id ORDER BY `created` DESC LIMIT 0,:count', array('gallery_id' => $gallery_id, 'count' => $count));
        else
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}gallery_items` ORDER BY `created` DESC LIMIT 0,:count', array('count' => $count));


        if ($count == 1)
        {
            $array = $SQL -> fetch();
            return new galleryItem('array', $array);

        } elseif ($count > 1) {
            $array = $SQL -> fetchAll();
            $oArray = array();

            foreach ($array as $item)
            {
                $oArray[] = new galleryItem('array', $item);
            }

            return $oArray;
        }
    }
}

function getGalleryItems($by, $limit=0, $limitFrom=0)
{
      global $panthera;
      return $panthera->db->getRows('gallery_items', $by, $limit, $limitFrom, 'galleryItem', 'id', 'DESC');
}

function getGalleryCategories($by, $limit=0, $limitFrom=0)
{
      global $panthera;
      return $panthera->db->getRows('gallery_categories', $by, $limit, $limitFrom, 'galleryCategory', 'id', 'DESC');
}

function removeGalleryItem($id)
{
    global $panthera;
    $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}gallery_items` WHERE `id` = :id', array('id' => $id));
    return (bool)$SQL->rowCount();
}

function removeGalleryCategory($id)
{
    global $panthera;
    $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}gallery_categories` WHERE `id` = :id', array('id' => $id));
    return (bool)$SQL->rowCount();
}

function createGalleryCategory($title, $login, $user_id, $language, $visibility, $user_full_name)
{
    global $panthera;
    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}gallery_categories` (`id`, `title`, `author_login`, `author_id`, `language`, `created`, `modified`, `visibility`, `author_full_name`, `thumb_id`, `thumb_url`) VALUES (NULL, :title, :author_login, :author_id, :language, NOW(), NOW(), :visibility, :author_full_name, "", "");', array('title' => $title, 'author_login' => $login, 'language' => $language, 'author_id' => $user_id, 'visibility' => $visibility, 'author_full_name' => $user_full_name));
    return (bool)$SQL->rowCount();
}

function createGalleryItem($title, $description, $link, $gallery_id, $visibility, $upload)
{
    global $panthera;

    $thumbnail = '';

    $panthera -> importModule('simpleImage');
    $fileInfo = pathinfo($link);
    $size = intval($panthera -> config -> getKey('gallery_thumbs_width', 240, 'int'));

    if ($size < 5)
        $size = 240;

    $thumb = pantheraUrl('{$upload_dir}/_thumbnails/' .$size. '_' .$fileInfo['filename']. '.jpg');

    if (!is_file($thumb))
    {
        $panthera -> logging -> output('createGalleryItem::Creating thumbnail for file "' .pantheraUrl($upload->location). '", width=' .$size);
        $simpleImage = new simpleImage();
        $simpleImage -> load(pantheraUrl($upload->location));
        $simpleImage -> resizeToWidth($size);
        $simpleImage -> save(PANTHERA_DIR. '/' .$thumb, IMAGETYPE_JPEG, 85);
        $panthera -> logging -> output('createGalleryItem::Saving thumbnail to file "' .PANTHERA_DIR. '/' .$thumb. '"');
    }

    $thumbnail = $panthera->config->getKey('url'). '/' .$thumb;

    $url_id = seoUrl(rand(99, 9999). '-' .$title);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}gallery_items` (`id`, `title`, `description`, `created`, `url_id`, `link`, `thumbnail`, `gallery_id`, `visibility`, `upload_id`) VALUES (NULL, :title, :description, NOW(), :url_id, :link, :thumbnail, :gallery_id, :visibility, :upload_id);', array('title' => $title, 'description' => $description, 'url_id' => $url_id, 'link' => $link, 'thumbnail' => $thumbnail, 'gallery_id' => $gallery_id, 'visibility' => $visibility, 'upload_id' => $upload->id));
    return (bool)$SQL->rowCount();
}

function galleryAjax()
{
      global $panthera, $user, $template;

      // Remeber you must change $tpl value...
      if ($_GET['display'] == 'gallery')
      {
            $panthera -> importModule('simpleImage');

            $tpl = 'gallery.tpl';

            $panthera->locale->setDomain('gallery');

            if (!getUserRightAttribute($user, 'can_view_galleryItem'))
            {
                  $template->display('no_access.tpl');
                  pa_exit();
            }

            if (@$_GET['action'] == 'delete_item')
            {
                $id = intval($_GET['image_id']);

                if (!getUserRightAttribute($user, 'can_edit_galleryItem') and !getUserRightAttribute($user, 'gallery_manage_img_' .$id))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                      pa_exit();
                }

                if (removeGalleryItem($id))
                {
                    print(json_encode(array('status' => 'success')));
                } else {
                    print(json_encode(array('status' => 'failed', 'error' => localize('Unknown error', 'messages'))));
                }

                pa_exit();
            }

            if (@$_GET['action'] == 'delete_category')
            {
                $id = intval($_GET['id']);

                if (!getUserRightAttribute($user, 'can_edit_galleryItem') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                      pa_exit();
                }

                if (removeGalleryCategory($id))
                {
                    print(json_encode(array('status' => 'success')));
                } else {
                    print(json_encode(array('status' => 'failed', 'error' => localize('Unknown error', 'messages'))));
                }

                pa_exit();
            }

            if (@$_GET['action'] == 'create_category') {

                if (!getUserRightAttribute($user, 'can_edit_galleryItem'))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                      pa_exit();
                }

                if ($_POST['title'] != '') {
                   if ($_POST['visibility'] == 1 or $_POST['visibility'] == 0) {
                        if (createGalleryCategory($_POST['title'], $user->login, $user->id, $user->language, $_POST['visibility'], $user->full_name)) {
                              print(json_encode(array('status' => 'success')));
                        } else {
                              print(json_encode(array('status' => 'failed', 'error' => localize('Unknown error', 'gallery'))));
                        }
                   }
                }
                pa_exit();
            }

            if (@$_GET['action'] == 'display_category') {
                  if (!isset($_GET['ctgid']))
                  {
                        pa_exit();
                  }

                  $template -> push('action', 'display_category');

                  $count = getGalleryItems(array('gallery_id' => $_GET['ctgid']), False);
                  $i = getGalleryItems(array('gallery_id' => $_GET['ctgid']), $count, 0);

                  $category = new galleryCategory('id', $_GET['ctgid']);
                  $template -> push('category_title', $category->title);
                  $template -> push('category_id', $category->id);
                  $template -> push('item_list', $i);
                  $template -> display($tpl);
                  pa_exit();

            }

            if (@$_GET['action'] == 'toggle_gallery_visibility') {
                if (!isset($_GET['ctgid']))
                    pa_exit();

                $id = intval($_GET['ctgid']);

                $item = new galleryCategory('id', $id);

                if ($item -> exists())
                {
                    $item -> visibility = !(bool)$item->visibility; // reverse bool value
                    print(json_encode(array('status' => 'success', 'visible' => $item->visibility)));
                } else {
                    print(json_encode(array('status' => 'failed', 'error' => localize('Category does not exists'))));
                }

                pa_exit();
            }

            if (@$_GET['action'] == 'toggle_item_visibility') {
                if (!isset($_GET['itid']))
                    pa_exit();

                $id = intval($_GET['itid']);

                $item = new galleryItem('id', $id);

                if ($item -> exists()) {
                      $item -> visibility = !(bool)$item->visibility;
                      print(json_encode(array('status' => 'success', 'visible' => $item -> visibility)));
                } else {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Item does not exists'))));
                }
                pa_exit();
            }

            if (@$_GET['action'] == 'edit_item_form') {
                $panthera -> importModule('filesystem');
                $template -> push('action', 'edit_item');

                if ($_GET['subaction'] == 'edit_item') {
                  $id = intval($_GET['id']);
                  $item = new galleryItem('id', $id);
                  $_POST['upload_id'] = intval($_POST['upload_id']);

                  if ($item -> exists()) {
                      $file = new uploadedFile('id', $_POST['upload_id']);

                      if (!$file -> exists())
                            ajax_exit(array('status' => 'failed', 'message' => localize('Selected file doesnt exists in upload list')));

                      if ($_POST['visibility'] == '1')
                              $item -> visibility = '1';
                      else
                              $item -> visibility = '0';

                      $item -> title = filterInput($_POST['title'], 'quotehtml');
                      $item -> description = filterInput($_POST['description'], 'quotehtml');
                      $item -> link = pantheraUrl($file->getLink());
                      $item -> thumbnail = $file->getThumbnail($panthera->config->getKey('gallery_thumbs_width', 240, 'int'), True);
                      $item -> upload_id = $_POST['upload_id'];

                      $category = new galleryCategory('id', $_POST['gallery_id']);

                      if ($category -> exists())
                        $item -> gallery_id = $_POST['gallery_id'];

                      print(json_encode(array('status' => 'success', 'ctgid' => $_POST['gallery_id'])));
                      pa_exit();

                  } else {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Error with changing item!'))));
                      pa_exit();
                  }
                }

                $id = intval($_GET['itid']);
                $item = new galleryItem('id', $id);

                if ($item -> exists()) {

                      $template -> push('id', $item -> id);
                      $template -> push('title', $item -> title);
                      $template -> push('description', $item -> description);
                      $template -> push('link', pantheraUrl($item -> link));
                      $template -> push('thumbnail', pantheraUrl($item -> thumbnail)); // We haven't script to make thumbnails yet. Later we'll delete this line. /M.
                      $template -> push('gallery_id', $item -> gallery_id);
                      $template -> push('visibility', $item -> visibility);
                      $template -> push('upload_id', $item -> upload_id);

                      $count = getGalleryCategories(array('language' => $user->language), False);
                      $c = getGalleryCategories(array('language' => $user->language), $count, 0);
                      $template -> push('category_list', $c);

                      $template -> display($tpl);
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
                            ajax_exit(array('status' => 'failed', 'message' => localize('Selected file doesnt exists in upload list')));

                        $link = pantheraUrl($file->getLink(), True);

                        if (createGalleryItem($_POST['title'], $_POST['description'], $link, intval($_POST['gallery_id']), $visibility, $file))
                              ajax_exit(array('status' => 'success', 'ctgid' => $_POST['gallery_id']));
                        else
                              ajax_exit(array('status' => 'failed', 'error' => localize('Error with adding category!', 'messages')));

                    } else {
                        ajax_exit(array('status' => 'failed', 'error' => localize('Please fill all form inputs')));
                    }

                    pa_exit();
                }

                $template -> push('action', 'add_item');

                $count = getGalleryCategories(array('language' => $user->language), False);
                $c = getGalleryCategories(array('language' => $user->language), $count, 0);

                $category = new galleryCategory('id', $_GET['ctgid']);
                if ($category -> exists())
                {
                    $template -> push('category_list', $c);
                    $template -> push('category_id', $_GET['ctgid']);
                    $template -> push('gallery_name', $category->title);
                    $template -> display($tpl);
                    pa_exit();
                }

            }

            if (@$_GET['action'] == 'add_category') {

                if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
                      pa_exit();
                }

                if ($_GET['new_title'] != '' and $_GET['visibility'] != '') {
                     createGalleryCategory($_GET['new_title'], $user->login, $user->id, $user->language, $_GET['visibility'], $user->full_name);
                     print(json_encode(array('status' => 'success')));
                } else {
                     print(json_encode(array('status' => 'failed', 'error' => localize('Error with adding category!', 'gallery'))));
                }
                pa_exit();
            }

            if (@$_GET['action'] == 'set_category_thumb') {
                if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'gallery'))));
                      pa_exit();
                }

                $id = intval($_GET['itid']);
                $ctgid = intval($_GET['ctgid']);

                $item = new galleryItem('id', $id);
                $category = new galleryCategory('id', $ctgid);

                if ($item -> exists() and $category -> exists()) {
                     $category -> thumb_id = $item -> id;
                     $category -> thumb_url = $item -> link;
                     print(json_encode(array('status' => 'success')));
                } else {
                     print(json_encode(array('status' => 'failed', 'error' => localize('Error with changing gallery thumbnail!', 'gallery'))));
                      pa_exit();
                }
                pa_exit();
            }

            if (@$_GET['action'] == 'edit_category') {

                if (!getUserRightAttribute($user, 'manage_gallery_categ') and !getUserRightAttribute($user, 'gallery_manage_cat_' .$id))
                {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'gallery'))));
                      pa_exit();
                }

                $id = intval($_GET['ctgid']);

                $item = new galleryCategory('id', $id);

                if ($item -> exists()) {


                      $response = array('status' => 'success');

                      if (isset($_GET['new_title']) and $_GET['new_title'] != '') {
                              $item -> title = filterInput($_GET['new_title'], 'quotehtml');
                              $response['title'] = filterInput($_GET['new_title'], 'quotehtml');
                      } else {
                              print(json_encode(array('status' => 'failed', 'error' => localize("Title can't be empty", 'gallery'))));
                              pa_exit();
                      }

                      if (isset($_GET['visibility'])) {

                              if ($_GET['visibility'] == 'show') {
                                    $item -> visibility = True;
                                    $response['visibility'] = 'show';
                              }

                              if ($_GET['visibility'] == 'hide') {
                                    $item -> visibility = False;
                                    $response['visibility'] = 'hide';
                              }
                      }

                      print(json_encode($response));

                } else {
                      print(json_encode(array('status' => 'failed', 'error' => localize('Category does not exists'))));
                      pa_exit();
                }
                pa_exit();
            }

            // normally we will display categories here
            $count = getGalleryCategories(array('language' => $user->language), False);
            $c = getGalleryCategories(array('language' => $user->language), $count, 0);

            $template -> push('category_list', $c);
            $template -> display($tpl);
            pa_exit();
      }
}


function galleryToAdminMenu($menu) { $menu -> add('gallery', localize('Gallery'), '?display=gallery', '', '', ''); }
$panthera -> add_option('admin_menu', 'galleryToAdminMenu');

function galleryToAjaxList($list) { $list[] = array('location' => 'plugins', 'name' => 'gallery', 'link' => '?display=gallery'); return $list; }
$panthera -> add_option('ajaxpages_list', 'galleryToAjaxList');

function galleryToDash($attr) {
    if ($attr[1] != "main") { return $attr; }
    $attr[0][] = array('link' => '?display=gallery', 'name' => localize('Gallery'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/gallery.png', 'linkType' => 'ajax');
    return $attr;
}

$panthera -> add_option('dash_menu', 'galleryToDash');

// add ajax subpage
$panthera -> add_option('ajax_page', 'galleryAjax');
?>
