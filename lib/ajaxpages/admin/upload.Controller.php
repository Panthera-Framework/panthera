<?php
/**
 * Upload management
 * Manager of uploaded files for Panthera Admin Panel
 *
 * @package Panthera\core\components\upload
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

class uploadAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.datasheet',
        'admin/ui.pager',
    );

    protected $uiTitlebar = array(
        'Upload management', 'upload',
    );

    protected $permissions = '';

    protected $actionPermissions = array(
        'addCategory' => array('admin.upload' => array('Upload administrator', 'upload'), 'admin.upload.addcategory'),
        'deleteCategory' => array('admin.upload' => array('Upload administrator', 'upload'), 'admin.upload.deletecategory', 'upload.delete.{$directory}'),
        'popupHandleFile' => array('admin.upload' => array('Upload administrator', 'upload'), 'admin.upload.insertfile', 'upload.manage.{$directory}', 'upload.upload.{$directory}'),
        'popupDelete' => _CONTROLLER_PERMISSION_INLINE_,
        'popupUploadFileWindow' => array('admin.upload' => array('Upload administrator', 'upload'), 'admin.upload.insertfile', 'upload.manage.{$directory}'),
        'saveSettings' => array('admin.upload' => array('Upload administrator', 'upload')),
        'editCategory' => array('admin.upload' => array('Upload administrator', 'upload'), 'upload.manage.{$directory}'),
    );


    /**
     * Save upload settings
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function saveSettingsAction()
    {
        $max = filesystem::sizeToBytes($_GET['maxFileSize']);

        if (!intval($max))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize("Invalid maximum file size", 'upload'),
            ));

        $this -> panthera -> config -> setKey('upload.maxsize', $max, 'int', 'upload');

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Edit category or display it's details
     *
     * @template upload.editCategory.tpl
     * @return null
     */

    public function editCategoryAction()
    {
        $category = new uploadCategory('name', $_GET['directory']);

        if (!$category -> exists())
        {
            /*ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Cannot find selected category', 'upload'),
            ));*/

            $this -> panthera -> template -> push('notfound', true);
        } else {
            $this -> panthera -> template -> push('category', $category);

            /**
             * Saving category details
             */

            if (isset($_POST['formSubmit']))
            {
                if (isset($_POST['mime']))
                    $category -> mime_type = $_POST['mime'];

                if (isset($_POST['name']) and $_POST['name'])
                    $category -> title = $_POST['name'];

                if (isset($_POST['maxfilesize']))
                    $category -> maxfilesize = $_POST['maxfilesize'];

                if ($category -> modified())
                {
                    $category -> save();

                    ajax_exit(array(
                        'status' => 'success',
                    ));
                } else {
                    ajax_exit(array(
                        'status' => 'failed',
                    ));
                }
            }
        }

        $this -> panthera -> template -> display('upload.editCategory.tpl');
        pa_exit();
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
        if (!strlen($_POST['title']))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Category title cannot be empty', 'upload'),
            ));

        if (intval($_POST['maxfilesize']) !== 0 and !filesystem::sizeToBytes($_POST['maxfilesize']))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid max filesize', 'upload'),
            ));

        if (!strlen($_POST['mime']))
            $_POST['mime'] = 'all';

        $name = Tools::seoUrl($_POST['title']). '-' .substr(md5(time().rand(999,9999)), 0, 4);

        // create upload directory and send success if created
        if (pantheraUpload::createUploadCategory($name, $this->panthera->user->id, $_POST['mime'], $_POST['title'], filesystem::sizeToBytes($_POST['maxfilesize'])))
            ajax_exit(array(
                'status' => 'success',
            ));

        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Ops! Something went badly wrong, site administrator have to check upload permissions and configuration', 'upload'),
        ));
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
        $category = $this -> panthera -> config -> getKey('upload.default.category', 'default', 'string', 'upload');

        if (isset($_GET['directory']))
            $category = $_GET['directory'];

        $category = new uploadCategory('name', $category);

        if (!$category -> exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Cannot find selected category', 'upload'),
            ));

        // @permissions: check if user has permissions to view this category
        $this -> checkPermissions('upload.view.' .$category -> name);

        $categoriesCount = uploadCategory::fetchAll('', false);
        $categories = uploadCategory::userFetchAll();

        // @defaults: check if there is any category, if not then create default categories
        if (!$categoriesCount and $this->checkPermissions('admin.upload', true))
        {
            // create important categories
            pantheraUpload::createUploadCategory($this -> panthera -> config -> getKey('upload.default.category', 'default', 'string', 'upload'), $this->panthera->user->id, 'all');
            pantheraUpload::createUploadCategory('gallery', $this->panthera->user->id, 'all');
            pantheraUpload::createUploadCategory('avatars', $this->panthera->user->id, 'all');
        }

        // create query statement
        $by = new whereClause();
        $by -> add( 'AND', 'category', '=', $category -> name);

        $this -> panthera -> template -> push('seeOtherUsersUploads', False);

        // if you are admin, you can see files which belong to other users
        if ($this -> panthera -> user -> isAdmin() and isset($_GET['otherUsers']))
        {
            $this -> panthera -> session -> set('pa.upload.otherusers', (bool)$_GET['otherUsers']);
        }

        // if user can see uploads posted by other users
        if ($this -> panthera -> session -> get('pa.upload.otherusers'))
            $this -> panthera -> template -> push('seeOtherUsersUploads', True);
        else // if not add filter
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

        /**
         * View switching
         */

        $viewType = $this -> panthera -> session -> get('upload.view.type.'.$directory);

        if (!$viewType) {
            $this -> panthera -> session -> set('upload.view.type.'.$directory, 'blank');
            $viewType = 'blank';
        }

        if (isset($_GET['changeView'])) {
            // check view
            $viewType = 'images';

            if ($_GET['changeView'] == 'blank')
                $viewType = 'blank';

            $this -> panthera -> session -> set('upload.view.type.'.$directory, $viewType);
        }

        // create variable responsible for change view of upload list
        $viewChange = 'blank';

        if ($viewType == 'blank')
            $viewChange = 'images';

        /**
         * Files listing
         */

        $files = uploadedFile::fetchAll($by, $limit[1], $limit[0]); // raw list
        $filesTpl = array(); // list passed to template

        $manageAllUploads = $this->checkPermissions('admin.upload', true);
        $canDeleteOwn = $this->checkPermissions('upload.deleteown', true);

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
            $icon = pantheraUrl($value->getThumbnail('200', True));
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
                'id' => $value->id,
                'object' => $value,
            );
        }

        if ($this -> checkPermissions('admin.upload.insertfile', true))
            $this -> panthera -> template -> push('upload_files', True);

        $callback = False;

        if (isset($_GET['callback']))
            $callback = True;

        // max_string_length = 27

        $this -> panthera -> template -> push(array(
            'callback' => $callback,
            'categories' => $categories,
            'setCategory' => $category -> name,
            'category' => $category,
            'max_file_size' => $this -> panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload'),
            'files' => $filesTpl,
            'view_type' => $viewType,
            'view_change' => $viewChange,
            'directory' => $directory,
            'callback_name' => $_GET['callback'],
            'user_login' => $this -> panthera -> user -> login,
            'isAdmin' => $this -> panthera -> user -> isAdmin(),
        ));

        $this -> panthera -> template -> display('upload.popup.tpl');
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
        // @handle: base64 encoded upload in post field "image" (HTML5 upload format)
        if (isset($_POST['image']))
        {
            $upload = pantheraUpload::parseEncodedUpload($_POST['image']);
            pantheraUpload::makeFakeUpload('input_file', $upload['content'], $_POST['fileName'], $upload['mime']);
            unset($_POST['image']);
            unset($upload);
        }

        // @validation: Upload file size
        /*if ($_FILES['input_file']['size'] > $this -> panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload') or filesize($_FILES['input_file']['tmp_name']) > $this->panthera->config->getKey('upload_max_size'))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('File is too big, allowed maximum size is: %s', 'upload', filesystem::bytesToSize($this -> panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload'))),
            ));*/

        /**
         * Check category
         */

        $category = $this -> panthera -> config -> getKey('upload.default.category', 'default', 'string', 'upload');

        // get category
        if (isset($_REQUEST['directory']))
            $category = $_REQUEST['directory'];

        $category = new uploadCategory('name', $category);

        if (!$category -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Category not found, or dont have permissions to upload files', 'upload'),
            ));
        }

        $countCategories = uploadCategory::fetchAll('', False, False);

        $categories = uploadCategory::fetchAll();

        if (!$countCategories)
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('There are no any upload categories', 'upload'),
            ));

        $description = filterInput($_POST['input_description'], 'quotehtml');
        $protected = 0;
        $public = 0;

        if ($_POST['protected'] == '1')
            $protected = 1;

        if (strlen($description) > 511)
            ajax_exit(array('status' => 'failed', 'message' => localize('Description is too long, out of 512 characters range')));

        try {
            $uploadID = pantheraUpload::handleUpload($_FILES['input_file'], $category -> name, $this->panthera->user->id, $this->panthera->user->login, $protected, $public, null, $description, false);
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                'message' => $e -> getMessage(),
                'code' => $e -> getCode(),
            ));

        }


        if ($uploadID)
            ajax_exit(array(
                'status' => 'success',
                'upload_id' => $uploadID,
            ));

        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Unknown error'),
        ));
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
        $categories = uploadCategory::fetchAll();
        $category = $this -> panthera -> config -> getKey('upload.default.category', 'default', 'string', 'upload');

        if (isset($_GET['directory']))
            $category = $_GET['directory'];

        // this object will provide extra informations such as max file size or mime type
        $uploadDirectory = new uploadCategory('name', $category);

        $this -> panthera -> template -> push(array(
            'setCategory' => $category,
            'categories' => $categories,
            'category' => $uploadDirectory,
        ));
        $this -> panthera -> template -> display('upload.popup.newfile.tpl');
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
            if ($file->author_id != $this->panthera->user->id and !$this->checkPermissions('admin.upload', True))
                $canDelete = false;

            // check if user can delete own uploads
            if (!$this->checkPermissions('upload.deleteown', True))
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

        // upload management can be only done by site administrator
        $this -> checkPermissions(array(
            'admin.upload' => array('Upload administrator', 'upload'),
            'admin.upload.addcategory' => array('Upload - adding categories', 'upload'),
            'admin.upload.deletecategory' => array('Upload - deleting categories', 'upload'),
        ));

        // just add to list
        $this -> checkPermissions(array(
            'admin.upload.insertfile' => array('Upload - uploading files to all categories', 'upload'),
        ), true);

        // initialize searchBar
        $searchBar = new uiSearchbar('uiTop');
        $searchBar -> setQuery($_GET['query']);
        $searchBar -> setAddress('?' .Tools::getQueryString('GET', '', array('_', 'page', 'query')));
        $searchBar -> navigate(True);
        $searchBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_upload,can_add_files', localize('Manage permissions'));

        $categories = uploadCategory::fetchAll();

        foreach ($categories as &$category)
        {
            $this -> checkPermissions(array(
                'upload.view.' .$category->name => slocalize('%s - view', 'upload', $category -> getName()),
                'upload.manage.' .$category->name => slocalize('%s - manage', 'upload', $category -> getName()),
                'upload.upload.' .$category->name => slocalize('%s - upload', 'upload', $category -> getName()),
            ));
        }

        $this -> panthera -> template -> push(array(
            'fileMaxSize' => filesystem::bytesToSize($this -> panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload')),
            'categories' => $categories
        ));
        $this -> panthera -> template -> display('upload.tpl');
        pa_exit();
    }
}