<?php

/**
 * Upload management
 * Manager of uploaded files for Panthera Admin Panel
 *
 * @package Panthera\core\upload\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
 */

class uploadAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.datasheet',
        'admin/ui.pager',
    );
    
    protected $uiTitlebar = array();
    
    protected $permissions = '';
        
    protected $actionPermissions = array(
        'addCategory' => array('admin.upload', 'admin.upload.addcategory'),
        'deleteCategory' => array('admin.upload', 'admin.upload.deletecategory'),
        'popupHandleFile' => array('admin.upload', 'admin.upload.insertfile'),
        'popupDelete' => _CONTROLLER_PERMISSION_INLINE_,
        'popupUploadFileWindow' => array('admin.upload', 'admin.upload.insertfile'),
        'saveSettings' => array('can_manage_upload'),
    );
    
    
    /**
     * Save upload settings
     *
     * @author Mateusz Warzyński
     * @return null
     */
    
    public function saveSettingsAction()
    {       
        $max = $_GET['maxFileSize'];
        
        if (!intval($max))
            ajax_exit(array('status' => 'failed', 'message' => localize("Failed. Please, increase your maximum file size.", 'upload')));
    
        $this -> panthera -> config -> setKey('upload_max_size', $max);
        ajax_exit(array('status' => 'success', 'message' => localize("Settings have been successfully saved!")));   
    }
    
    
    
    /**
     * Delete upload category function
     *
     * @author Mateusz Warzyński 
     * @author Damian Kęska
     * @return null
     */
    
    public function deleteCategoryAction()
    {
        if (!strlen($_GET['id']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Id is empty!', 'upload')));

        if (pantheraUpload::deleteUploadCategory($_GET['id']))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('You have not permission to perform this action!', 'upload')));
        
        ajax_exit(array('status' => 'failed'));
    }
    
    
    
    /**
     * Add category function
     *
     * @author Mateusz Warzyński 
     * @author Damian Kęska
     * @return null
     */
    
    public function addCategoryAction()
    {
        if (!strlen($_POST['name']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Name is empty.', 'upload')));
        
        if (!strlen($_POST['mime']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Mime is empty.', 'upload')));
        
        if (pantheraUpload::createUploadCategory($_POST['name'], $this->panthera->user->id, $_POST['mime']))
            ajax_exit(array('status' => 'success'));

        ajax_exit(array('status' => 'failed'));
    }
    
    
    
    /**
     * List of uploaded files
     *
     * @author Mateusz Warzyński 
     * @author Damian Kęska
     * @return string
     */
    
    public function popupDisplay()
    {
        $category = 'default';
        
        if (isset($_GET['directory']))
            $category = $_GET['directory'];
        
        // @permissions: check if user has permissions to view this category
        $this -> checkPermissions('upload.view.' .$category);
        
        $categoriesCount = uploadCategory::fetchAll('', false);
        $categories = uploadCategory::userFetchAll();
        
        if (!$categoriesCount) {
            // create important categories
            if ($this->checkPermissions('admin.upload')) {
                pantheraUpload::createUploadCategory('default', $this->panthera->user->id, 'all');
                pantheraUpload::createUploadCategory('gallery', $this->panthera->user->id, 'all');
                pantheraUpload::createUploadCategory('avatars', $this->panthera->user->id, 'all');
            }  else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot create default category. Check your permissions!')));
            }
        }
        
        foreach ($categories as $c => $d)
            $categoryList[$d -> name] = True;
        
        if (!isset($categoryList[$category]))
            ajax_exit(array('status' => 'failed', 'message' => localize('Given category is invalid!')));
        
        // create query statement
        $by = new whereClause();
        $by -> add( 'AND', 'category', '=', $category);
        
        $this -> panthera -> template -> push('seeOtherUsersUploads', False);
        
        // if you are admin, you can see files which belong to other users
        if (checkUserPermissions($this->panthera->user, True) and isset($_GET['otherUsers']))
        {
            if ($_GET['otherUsers'] == 'true')
                $this -> panthera -> session -> set('pa.upload.otherusers', true);
            else
                $this -> panthera -> session -> set('pa.upload.otherusers', false);
        }
        
        if ($this -> panthera -> session -> get('pa.upload.otherusers'))
            $this -> panthera -> template -> push('seeOtherUsersUploads', True);
        else
            $by -> add( 'AND', 'uploader_login', '=', $this -> panthera -> user -> login);
        
        $page = intval(@$_GET['page']);
        $count = uploadedFile::fetchAll($by, False);
    
        if ($page < 0)
            $page = 0;
            
        // pager
        $uiPager = new uiPager('adminUpload', $count, 'adminUpload', 16);
        $uiPager -> setActive($page); // ?display=upload&cat=admin&popup=true&action=display_list
        $uiPager -> setLinkTemplatesFromConfig('upload.tpl');
        $limit = $uiPager -> getPageLimit();
        
        $viewType = $this -> panthera -> session -> get('upload.view.type.'.$directory);
    
        if (!$viewType) {
            $this -> panthera -> session -> set('upload.view.type.'.$directory, 'blank');
            $viewType = 'blank';
        }
        
        if (isset($_GET['changeView'])) {
            // check view
            if ($_GET['changeView'] == 'blank')
                $viewType = 'blank';
            else
                $viewType = 'images';
            
            $this -> panthera -> session -> set('upload.view.type.'.$directory, $viewType);
        }
        
        // create variable responsible for change view of upload list
        if ($viewType == 'blank')
            $viewChange = 'images';
        else
            $viewChange = 'blank';

        $files = uploadedFile::fetchAll($by, $limit[1], $limit[0]); // raw list
        $filesTpl = array(); // list passed to template
    
        $manageAllUploads = $this->checkPermissions('can_manage_all_uploads');
        $canDeleteOwn = $this->checkPermissions('can_delete_own_uploads');
    
        foreach ($files as $key => $value)
        {
            if ($value->uploader_id != $this->panthera->user->id and !$manageAllUploads and !$value->__get('public'))
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
            $url = $this -> panthera -> config -> getKey('url');
            $location = pantheraUrl($value->location);
            $icon = '';
            $ableToDelete = False; // can user delete this file?
            $link = $value->getLink();
    
            // getting icon by mime type
            $fileType = filesystem::fileTypeByMime($value->mime);
            $icon = pantheraUrl($value->getThumbnail('200'));
            $this -> panthera -> logging -> output ('Checking for icon: ' .$icon. ' for type ' .$fileType, 'upload');
            
            // give user rights to delete file, create the button
            if (($this->panthera->user->id == $value->uploader_id and $canDeleteOwn) or $manageAllUploads)
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
    
        if ($this -> checkPermissions('can_upload_files'))
            $this -> panthera -> template -> push('upload_files', True);
    
        if (isset($_GET['callback']))
            $callback = True;
        else
            $callback = False;
    
        // max_string_length = 27
    
        $this -> panthera -> template -> push('callback', $callback);
        $this -> panthera -> template -> push('categories', $categories);
        $this -> panthera -> template -> push('setCategory', $category);
        $this -> panthera -> template -> push('max_file_size', $this -> panthera -> config -> getKey('upload_max_size', 3145728, 'int')); // default 3 mbytes
        $this -> panthera -> template -> push('files', $filesTpl);
        $this -> panthera -> template -> push('view_type', $viewType);
        $this -> panthera -> template -> push('view_change', $viewChange);
        $this -> panthera -> template -> push('directory', $directory);
        $this -> panthera -> template -> push('callback_name', $_GET['callback']);
        $this -> panthera -> template -> push('user_login', $this -> panthera -> user -> login);
        $this -> panthera -> template -> display('upload_popup.tpl');
        pa_exit();
    }


    
    /**
     * Handle file upload
     *
     * @author Damian Kęska
     * @return null
     */
    
    public function popupHandleFileAction()
    {   
        // handle base64 encoded upload in post field "image"
        if (isset($_POST['image']))
        {
            $upload = pantheraUpload::parseEncodedUpload($_POST['image']);
            
            pantheraUpload::makeFakeUpload('input_file', $upload['content'], $_POST['fileName'], $upload['mime']);

            unset($_POST['image']);
            unset($upload);
        }

        if ($_FILES['input_file']['size'] > $this->panthera->config->getKey('upload_max_size') or filesize($_FILES['input_file']['tmp_name']) > $this->panthera->config->getKey('upload_max_size'))
            ajax_exit(array('status' => 'failed', 'message' => localize('File is too big, allowed maximum size is:'). ' ' .filesystem::bytesToSize($this->panthera->config->getKey('upload_max_size'))));

        if (isset($_GET['directory']))
            $category = $_GET['directory'];
        else 
            $category = $_POST['input_category'];
        
        $countCategories = pantheraUpload::fetchAll('', False, False);
        
        if (!$countCategories)
            ajax_exit(array('status' => 'failed', 'message' => localize('There is no created upload category!', 'upload')));

        $categories = pantheraUpload::fetchAll('', $countCategories, 0);
        
        foreach ($categories as $c)
            $categoryList[$c['name']] = True;
        
        if (!array_key_exists($category, $categoryList)) {
            
            // create upload category, specially for this upload
            if (!pantheraUpload::createUploadCategory($category, $this->panthera->user->id, 'all'))
                ajax_exit(array('status' => 'failed', 'message' => localize('Given category does not exist!', 'upload')));

        }
        
        // get mime type of file
        $mime = filesystem::getFileMimeType($_FILES['input_file']['tmp_name']);

        $description = filterInput($_POST['input_description'], 'quotehtml');
        $protected = 0;
        $public = 0;

        if (strlen($description) > 511)
            ajax_exit(array('status' => 'failed', 'message' => localize('Description is too long, out of 512 characters range')));
        
        $uploadID = pantheraUpload::handleUpload($_FILES['input_file'], $category, $this->panthera->user->id, $this->panthera->user->login, $protected, $public, $mime, $description);

        if ($uploadID)
            ajax_exit(array('status' => 'success', 'upload_id' => $uploadID));
        
        ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error')));
    }


    
    /**
     * Display file upload page
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return string
     */
    
    public function popupUploadFileWindowAction()
    {
        $categories = pantheraUpload::fetchAll();
        
        if (isset($_GET['directory']))
            $category = $_GET['directory'];
        else
            $category = 'default';
        
        $this -> panthera -> template -> push('setCategory', $category);
        $this -> panthera -> template -> push('categories', $categories);
        
        $this -> panthera -> template -> display('upload_popup_newfile.tpl');
        pa_exit();
    }
    
    
    
    /**
     * Delete selected files
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return void
     */
    
    public function popupDeleteAction()
    {
        $ids = explode(",", $_GET['id']);
        
        $deleted = 0;
        
        foreach ($ids as $id)
        {   
            // get file object from database
            $file = new uploadedFile('id', intval($id));
            
            // define control variable
            $canDelete = true;

            if (!$file -> exists())
                $canDelete = false;
            
    
            // check if user is author or just can manage all uploads
            if ($file->author_id != $this->panthera->user->id and !$this->checkPermissions('can_manage_all_uploads', True))
                $canDelete = false;
    
            // check if user can delete own uploads
            if (!$this->checkPermissions('can_delete_own_uploads', True))
                $canDelete = false;
                
            if ($canDelete) {
                if (pantheraUpload::deleteUpload(intval($id), $file->location))
                    $deleted = $deleted+1;
            }
        } 
        
        if ($deleted == count($ids))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'success', 'message' => localize("Cannot delete some files!", 'upload')));
    }


    
    public function display()
    {
        // load language domain
        $this -> panthera -> locale -> loadDomain('files');
        
        // check if action is given
        $this -> dispatchAction();
        
        // display popup if defined
        if ($_GET['popup'])
            $this -> popupDisplay();
        
        // send permissions data to template
        // $this -> panthera -> template -> push ('permissions', $this->userPermissions);
        
        // initialize searchBar
        $searchBar = new uiSearchbar('uiTop');
        $searchBar -> setQuery($_GET['query']);
        $searchBar -> setAddress('?' .getQueryString('GET', '', array('_', 'page', 'query')));
        $searchBar -> navigate(True);
        $searchBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_upload,can_add_files', localize('Manage permissions'));
        
        $this -> panthera -> template -> push('fileMaxSize', $this->panthera->config->getKey('upload_max_size'));
        
        $countCategories = pantheraUpload::fetchAll('', False, False);
        $categories = pantheraUpload::fetchAll('', $countCategories, 0);
        $this -> panthera -> template -> push('categories', $categories);
        
        $titlebar = new uiTitlebar(localize('Upload management'));
        
        $this -> panthera -> template -> display('upload.tpl');
        pa_exit();
    }
}
