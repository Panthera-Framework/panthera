<?php
/**
  * Upload system
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

$panthera -> importModule('filesystem');
$panthera -> importModule('simpleImage');
$panthera -> importModule('pager');
$panthera -> locale -> loadDomain('files');

$permissions = array(
    'admin' => checkUserPermissions($panthera->user, True),
);

$canManageUpload = getUserRightAttribute($user, 'can_manage_upload');
// $canManageUploadCategories = getUserRightAttribute($user, 'can_manage_upload_categories');
$canAddFiles = getUserRightAttribute($user, 'can_add_files');

$panthera -> template -> push ('permissions', $permissions);

if (isset($_GET['popup']))
{   
    if (!checkUserPermissions($panthera->user))
    {
        $noAccess = new uiNoAccess; $noAccess -> display();
        $panthera->finish();
        pa_exit();
    }

    // ==== DELETE UPLOADS ====

    /**
      * Delete selected files
      *
      * @author Mateusz Warzyński
      * @author Damian Kęska
      */
    
    if ($_GET['action'] == 'delete')
    {
        $ids = explode(",", $_GET['id']);
        
        $files = count($ids);
        $deleted = 0;
        
        foreach ($ids as $id)
        {
            $file = new uploadedFile('id', $id);
            
            $canDelete = true;
    
            // check if file exists
            if (!$file -> exists())
                $canDelete = false;
                //ajax_exit(array('status' => 'failed', 'message' => localize('File does not exists', 'files')));
    
            // check if user is author or just can manage all uploads
            if ($file -> author_id != $user -> id and !getUserRightAttribute($user, 'can_manage_all_uploads'))
                $canDelete = false;
                //ajax_exit(array('status' => 'failed', 'message' => localize('You are not allowed to manage other users uploads', 'files')));
    
            // check if user can delete own uploads
            if (!getUserRightAttribute($user, 'can_delete_own_uploads'))
                $canDelete = false;
                //ajax_exit(array('status' => 'failed', 'message' => localize('You dont have permissions to delete your own uploads', 'files')));
            if ($canDelete) {
                if (pantheraUpload::deleteUpload($id, $file->location))
                    $deleted = $deleted+1;
            }
    
            // maybe permissions error?
            //$panthera -> logging -> output ('upload::Cannot delete upload, check permissions of a file ' .$file->location);
            // ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error')));
        } 
        
        if ($deleted == $files)
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'success', 'message' => localize("Cannot delete some files!", 'upload')));
    }
    
    /**
      * Display file upload page
      *
      * @author Mateusz Warzyński
      * @author Damian Kęska
      */
    
    if ($_GET['action'] == 'uploadFileWindow')
    {
        if (!getUserRightAttribute($user, 'can_upload_files'))
            ajax_exit(array('status' => 'failed', 'message' => localize('You are not allowed to upload files', 'files')));
        
        $countCategories = pantheraUpload::getUploadCategories('', False, False);
        $categories = pantheraUpload::getUploadCategories('', $countCategories, 0);
        
        if (isset($_GET['directory']))
            $category = $_GET['directory'];
        else
            $category = 'default';
        
        $panthera -> template -> push('setCategory', $category);
        $panthera -> template -> push('categories', $categories);
        $panthera -> template -> display('upload_popup_newfile.tpl');
        pa_exit();
    }


    /**
      * Handle file upload
      *
      * @author Damian Kęska
      */

    if ($_GET['action'] == 'handle_file')
    {
        if (!getUserRightAttribute($user, 'can_upload_files'))
            ajax_exit(array('status' => 'failed', 'message' => localize('You are not allowed to upload files', 'files')));
        
        // handle base64 encoded upload in post field "image"
        if (isset($_POST['image']))
        {
            $upload = pantheraUpload::parseEncodedUpload($_POST['image']);
            pantheraUpload::makeFakeUpload('input_file', $upload['content'], $_POST['fileName'], $upload['mime']);

            //$_FILES['input_file'] = array('tmp_name' => '/tmp/' .md5($_POST['image']), 'name' => $_POST['fileName'], 'type' => $upload['mime'], 'error' => 0, 'size' => strlen($upload['content']));
            //$fp = fopen($_FILES['input_file']['tmp_name'], 'w');
            //fwrite($fp, base64_decode($_POST['image']));
            //fclose($fp);
            unset($_POST['image']);
            unset($upload);
        }

        if ($_FILES['input_file']['size'] > $panthera -> config -> getKey('upload_max_size') or filesize($_FILES['input_file']['tmp_name']) > $panthera -> config -> getKey('upload_max_size'))
            ajax_exit(array('status' => 'failed', 'message' => localize('File is too big, allowed maximum size is:'). ' ' .filesystem::bytesToSize($panthera -> config -> getKey('upload_max_size'))));

        if (isset($_GET['directory']))
            $category = $_GET['directory'];
        else 
            $category = $_POST['input_category'];
        
        $countCategories = pantheraUpload::getUploadCategories('', False, False);
        
        if (!$countCategories)
            ajax_exit(array('status' => 'failed', 'message' => localize('There is no created upload category!', 'upload')));

        $categories = pantheraUpload::getUploadCategories('', $countCategories, 0);
        
        foreach ($categories as $c)
            $categoryList[$c['name']] = True;
        
        if (!array_key_exists($category, $categoryList)) {
            if ($canManageUpload or $permissions['admin']) { 
                if (!pantheraUpload::createUploadCategory($category, $panthera->user->id, 'all'))
                    ajax_exit(array('status' => 'failed', 'message' => localize('Given category does not exist!', 'upload')));
            }
        }
        
        $mime = '';

        // get mime type
        if(function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['input_file']['tmp_name']);
            finfo_close($finfo);

        } elseif(function_exists('mime_content_type'))
            $mime = @mime_content_type($_FILES['input_file']['tmp_name']); // this function is deprecated, so we will try to use finfo or just to get mime type from user...
        else
            $mime = $file['type']; // this may be a little bit dangerous but... we dont have any option

        if ($mime == '' or $mime == NuLL)
            $mime = filesystem::getFileMimeType($_FILES['input_file']['name']);

        $description = filterInput($_POST['input_description'], 'quotehtml');
        $protected = 0;
        $public = 0;

        if (strlen($description) > 511)
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Description is too long, out of 512 characters range')));
        }
        
        $uploadID = pantheraUpload::handleUpload($_FILES['input_file'], $category, $user->id, $user->login, $protected, $public, $mime, $description);

        if ($uploadID)
        {
            ajax_exit(array('status' => 'success', 'upload_id' => $uploadID));
        }
        
        ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error')));
    }


    /**
      * List of uploaded files
      *
      * @author Mateusz Warzyński 
      * @author Damian Kęska
      */
    
    if (!isset($_GET['directory']))
        $category = 'default';
    else
        $category = $_GET['directory'];
    
    $countCategories = pantheraUpload::getUploadCategories('', False, False);
        
    if (!$countCategories) {
        if ($canManageUpload or $permissions['admin']) {
            pantheraUpload::createUploadCategory('default', $panthera->user->id, 'all');
            $countCategories = 1;
        }  else {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot create default category. Check your permissions!')));
        }
    }

    $categories = pantheraUpload::getUploadCategories('', $countCategories, 0);
    
    foreach ($categories as $c)
        $categoryList[$c['name']] = True;
        
    if (!array_key_exists($category, $categoryList))
        ajax_exit(array('status' => 'failed', 'message' => localize('Given category is invalid!')));
    
    $by = new whereClause();
    
    $by -> add( 'AND', 'category', '=', $category);
    
    $panthera -> template -> push('seeOtherUsersUploads', False);
    
    if ($permissions['admin'] and isset($_GET['otherUsers']))
    {
        if ($_GET['otherUsers'] == 'true')
        {
            $panthera -> session -> set('pa.upload.otherusers', true);
        } else {
            $panthera -> session -> set('pa.upload.otherusers', false);
        }
    }
    
    if ($panthera->session->get('pa.upload.otherusers'))
        $panthera -> template -> push('seeOtherUsersUploads', True);
    else
        $by -> add( 'AND', 'uploader_login', '=', $panthera -> user -> login);
    
    $page = intval(@$_GET['page']);
    $count = pantheraUpload::getUploadedFiles($by, False);

    if ($page < 0)
        $page = 0;
        
    // pager
    $uiPager = new uiPager('adminUpload', $count, 'adminUpload', 16);
    $uiPager -> setActive($page); // ?display=upload&cat=admin&popup=true&action=display_list
    $uiPager -> setLinkTemplatesFromConfig('upload.tpl');
    $limit = $uiPager -> getPageLimit();
    
    $viewType = $panthera -> session -> get('upload.view.type.'.$directory);

    if (!$viewType) {
        $panthera -> session -> set('upload.view.type.'.$directory, 'blank');
        $viewType = 'blank';
    }
    
    if (isset($_GET['changeView'])) {
        
        if ($_GET['changeView'] == 'blank') {
            $viewType = 'blank';
        } else {
            $viewType = 'images';
        }
        
        $panthera -> session -> set('upload.view.type.'.$directory, $viewType);
    }
    
    if ($viewType == 'blank')
        $viewChange = 'images';
    else
        $viewChange = 'blank';

    $files = pantheraUpload::getUploadedFiles($by, $limit[1], $limit[0]); // raw list
    $filesTpl = array(); // list passed to template

    foreach ($files as $key => $value)
    {
        if ($value -> uploader_id != $user -> id and !getUserRightAttribute($user, 'can_manage_all_uploads') and !$value -> __get('public'))
            continue;

        $name = filesystem::mb_basename($value->location);
        
        if ($viewType == 'images') {
            // cut string
            if (strlen($name) > 13) {
                $string = explode(".", $name);
                $name = substr($name, 0, 13)."...".end($string);
            }
        }
        
        // get site url
        $url = $panthera -> config -> getKey('url');
        $location = pantheraUrl($value->location);
        $icon = '';
        $ableToDelete = False; // can user delete this file?
        $link = $value->getLink();

        // getting icon by mime type
        $fileType = filesystem::fileTypeByMime($value->mime);
        $icon = $value->getThumbnail('200');
        $panthera -> logging -> output ('Checking for icon: ' .$icon. ' for type ' .$fileType, 'upload');
        
        // give user rights to delete file, create the button
        if (($user->id == $value->uploader_id and getUserRightAttribute($user, 'can_delete_own_uploads')) or getUserRightAttribute($user, 'can_manage_all_uploads'))
            $ableToDelete = True;

        $filesTpl[] = array(
            'name' => $name,
            'mime' => $value->mime,
            'description' => $value->description,
            'location' => $location,
            'link' => $link,
            'uploader_login' => $value->uploader_login,
            'ableToDelete' => $ableToDelete,
            'icon' => $icon,
            'author' => $value->uploader_login,
            'directory' => $value->category,
            'type' => $fileType,
            'id' => $value->id
        );
    }

    if (getUserRightAttribute($user, 'can_upload_files'))
    {
        $template -> push('upload_files', True);
    }

    if (isset($_GET['callback']))
        $callback = True;
    else
        $callback = False;

    // max_string_length = 27

    $panthera -> template -> push('callback', $callback);
    $panthera -> template -> push('categories', $categories);
    $panthera -> template -> push('setCategory', $category);
    $panthera -> template -> push('max_file_size', $panthera -> config -> getKey('upload_max_size', 3145728, 'int')); // default 3 mbytes
    $panthera -> template -> push('files', $filesTpl);
    $panthera -> template -> push('view_type', $viewType);
    $panthera -> template -> push('view_change', $viewChange);
    $panthera -> template -> push('directory', $directory);
    $panthera -> template -> push('callback_name', $_GET['callback']);
    $panthera -> template -> push('user_login', $user->login);
    $panthera -> template -> display('upload_popup.tpl');
    pa_exit();
}


if ($_GET['action'] == 'addCategory')
{
    if (!strlen($_POST['name']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Name is empty.', 'upload')));
    
    if (!strlen($_POST['mime']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Mime is empty.', 'upload')));
    
    if ($canManageUpload or $permissions['admin']) {
        if (pantheraUpload::createUploadCategory($_POST['name'], $panthera->user->id, $_POST['mime']))
            ajax_exit(array('status' => 'success'));
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('You have not permissions to create upload category.', 'upload')));
    }
    
    ajax_exit(array('status' => 'failed'));
    
} elseif ($_GET['action'] == 'deleteCategory')
{
    if (!strlen($_GET['id']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Id is empty!', 'upload')));
    
    if ($canManageUpload or $permissions['admin']) {
        if (pantheraUpload::deleteUploadCategory($_GET['id']))
            ajax_exit(array('status' => 'success'));
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('You have not permission to perform this action!', 'upload')));
    }
    
    ajax_exit(array('status' => 'failed'));
} elseif ($_GET['action'] == 'set_mime')
{
    ajax_exit(array('status' => 'failed'));
} elseif ($_GET['action'] == 'saveSettings') {
        
    if (!$permissions['admin'])
        ajax_exit(array('status' => 'failed', 'message' => localize("You haven't permission to execute this function!", 'upload')));
        
    $max = $_GET['maxFileSize'];
    
    if (!intval($max)) {
        ajax_exit(array('status' => 'failed', 'message' => localize("Failed. Please, increase your maximum file size.", 'upload')));
    }

    $panthera -> config -> setKey('upload_max_size', $max);
    ajax_exit(array('status' => 'success', 'message' => localize("Settings have been successfully saved!")));
    
} else {
    $sBar = new uiSearchbar('uiTop');
    $sBar -> setQuery($_GET['query']);
    $sBar -> setAddress('?' .getQueryString('GET', '', array('_', 'page', 'query')));
    $sBar -> navigate(True);
    $sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_upload,can_add_files', localize('Manage permissions'));
    
    $panthera -> template -> push('fileMaxSize', $panthera -> config -> getKey('upload_max_size'));
    
    $countCategories = pantheraUpload::getUploadCategories('', False, False);
    $categories = pantheraUpload::getUploadCategories('', $countCategories, 0);
    $panthera -> template -> push('categories', $categories);
    
    $titlebar = new uiTitlebar(localize('Upload management'));
    $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/uploads.png', 'left');
    
    $panthera -> template -> display('upload.tpl');
    pa_exit();
}

pa_exit();
