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
    'admin' => checkUserPermissions($panthera->user, True)
);

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
            
        $panthera -> template -> display('upload_newfile.tpl');
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

        $directory = 'default';
        
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
        
        $uploadID = pantheraUpload::handleUpload($_FILES['input_file'], $directory, $user->id, $user->login, $protected, $public, $mime, $description);

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
        $directory = 'default';
    else
        $directory = $_GET['directory'];
    
    $by = new whereClause();
    $by -> add( 'AND', 'uploader_login', '=', $panthera -> user -> login);
    $by -> add( 'AND', 'category', '=', $directory);
    
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
    {
        $by = '';
        $panthera -> template -> push('seeOtherUsersUploads', True);
    }
    
    $page = intval(@$_GET['page']);
    $count = pantheraUpload::getUploadedFiles($by, False);

    if ($page < 0)
        $page = 0;
        
    // pager
    $uiPager = new uiPager('adminUpload', $count, 'adminUpload', 16);
    $uiPager -> setActive($page); // ?display=upload&cat=admin&popup=true&action=display_list
    $uiPager -> setLinkTemplatesFromConfig('upload.tpl');
    $limit = $uiPager -> getPageLimit();

    $files = pantheraUpload::getUploadedFiles($by, $limit[1], $limit[0]); // raw list
    $filesTpl = array(); // list passed to template

    foreach ($files as $key => $value)
    {
        if ($value -> uploader_id != $user -> id and !getUserRightAttribute($user, 'can_manage_all_uploads') and !$value -> __get('public'))
            continue;

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
            'name' => filesystem::mb_basename($value->location),
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
    
    $panthera -> template -> push('max_file_size', $panthera -> config -> getKey('upload_max_size', 3145728, 'int')); // default 3 mbytes
    $panthera -> template -> push('files', $filesTpl);
    $panthera -> template -> push('view_type', $viewType);
    $panthera -> template -> push('view_change', $viewChange);
    $panthera -> template -> push('directory', $directory);
    $panthera -> template -> push('callback_name', $_GET['callback']);
    $panthera -> template -> push('user_login', $user->login);
    $panthera -> template -> display('upload.tpl');
    pa_exit();
}

pa_exit();
