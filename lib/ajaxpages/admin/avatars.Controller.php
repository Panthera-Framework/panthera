<?php
/**
 * Avatars management
 * Manager of avatars, uploaded images to `avatars` upload category
 *
 * @package Panthera\core\adminUI\avatars
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Avatars management
 * Manager of avatars, uploaded images to `avatars` upload category
 *
 * @package Panthera\core\adminUI\avatars
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

class avatarsAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.pager'
    );

    protected $actionPermissions = array();

    protected $permissions = '';

    protected $uiTitlebar = array();



    /**
     * Upload avatar, check dimensions, add item to database
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function uploadAvatarAction()
    {
        if (isset($_POST['image'])) {
            $upload = pantheraUpload::parseEncodedUpload($_POST['image']);

            pantheraUpload::makeFakeUpload('input_file', $upload['content'], $_POST['fileName'], $upload['mime']);

            unset($_POST['image']);
            unset($upload);
        }

        // check file size
        if ($_FILES['input_file']['size'] > $this->panthera->config->getKey('upload_max_size') or filesize($_FILES['input_file']['tmp_name']) > $this->panthera->config->getKey('upload_max_size'))
            ajax_exit(array('status' => 'failed', 'message' => localize('File is too big, allowed maximum size is:').' '.filesystem::bytesToSize($this->panthera->config->getKey('upload_max_size'))));


        // get upload object
        $avatarsUpload = $this -> checkAvatarCategory();

        // get mime type
        $mime = filesystem::getFileMimeType($_FILES['input_file']['name']);

        // get set dimensions from database
        $d = $this->panthera->config->getKey('avatar_dimensions');
        $dimensions = explode('x', $d);

        // get uploaded image as instance
        $image = new SimpleImage();
        $image -> load($_FILES['input_file']['tmp_name']);

        // check dimensions of image with ones set in database
        if ($image->getWidth() != intval($dimensions[0]) or $image->getHeight() != intval($dimensions[1]))
            ajax_exit(array('status' => 'failed', 'message' => localize('Dimensions are incorrect. It needs to be: ').$d));

        // upload file, add record to database
        $fileID = pantheraUpload::handleUpload($_FILES['input_file'], $avatarsUpload->name, $this->panthera->user->id, $this->panthera->user->login, 0, 0, $mime, '');

        $file = new uploadedFile('id', $fileID);

        // check if file exists
        if (!$file -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot handle file to avatars category.', 'avatars')));

        ajax_exit(array('status' => 'success'));
    }



    /**
     * Delete avatar
     *
     * @author Mateusz Warzyński
     * @return bool
     */

    public function deleteAction()
    {
        // get file from database as object
        $file = new uploadedFile('id', $_GET['id']);

        if ($file->uploader_id != $this->panthera->user and !checkUserPermissions($this->panthera->user, True) and !$this->checkPermissions('can_manage_upload', True))
            ajax_exit('You do not have permission to execute this action.', 'avatars');

        if ($file -> exists()) {
            // delete file from upload
            if (pantheraUpload::deleteUpload($file->id, $file->location))
                ajax_exit(array('status' => 'success'));
            else
                ajax_exit(array('status' => 'failed', 'message' => localize('Something went wrong. Could not delete avatar, sorry.', 'avatars')));
        } else {
            ajax_exit(array('status' => 'failed', 'message' => localize('File does not exist.', 'avatars')));
        }
    }



    /**
     * Display all avatars, that user is able to use
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function displayAvatarsAction()
    {
        $upload = $this -> checkAvatarCategory();

        // create query
        $by = new whereClause();
        $by -> add( 'AND', 'uploader_id', '=', $this->panthera->user->id);
        $by -> add( 'AND', 'category', '=', $upload->name);

        // get avatars
        $items = uploadedFile::fetchAll($by);

        // check if user does not over the limit
        $canUpload = (count($items) < $this->panthera->config->getKey('avatars.maxAvatars', 15, 'int', 'avatars'));

        // send items data to template
        $this -> panthera -> template -> push('avatars', $items);
        $this -> panthera -> template -> push('canUpload', $canUpload);

        // send information about default avatar
        $this -> panthera -> template -> push('defaultAvatarLocation', pantheraUrl('{$PANTHERA_URL}/images/default_avatar.png'));
        $this -> panthera -> template -> push('defaultAvatarId', '_1');

        if (isset($_GET['callback']))
            $callback = True;
        else
            $callback = False;

        $d = $this -> panthera -> config -> getKey('avatar_dimensions');
        $dimensions = explode('x', $d);

        $this -> panthera -> template -> push('callback', $callback);
        $this -> panthera -> template -> push('callback_name', $_GET['callback']);
        $this -> panthera -> template -> push('dimensions', $dimensions);

        $this -> panthera -> template -> display('avatarsPopup.tpl');
        pa_exit();
    }



    /**
     * Get avatar categories objects (upload, category)
     *
     * @author Mateusz Warzyński
     * @return object
     */

    protected function checkAvatarCategory()
    {
        // get avatars upload category
        $avatarsUpload = new uploadCategory('name', 'avatars');

        // if category does not exist, create it
        if (!$avatarsUpload -> exists())
        {
            if (!pantheraUpload::createUploadCategory('avatars', $this->panthera->user->id, array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp')))
                ajax_exit(array('status' => 'failed', 'message' => localize('Error! Upload category for avatars is not created.', 'avatars')));

            else
                $avatarsUpload = new uploadCategory('name', 'avatars');

            if ($avatarsUpload -> exists())
                return $avatarsUpload;

        } else {
            return $avatarsUpload;
        }

        return False;
    }



    /**
     * Main function that should return result
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function display()
    {
        $this -> panthera -> locale -> loadDomain('avatars');

        $this -> dispatchAction();

        $this -> panthera -> template -> display('avatarsPopup.tpl');
    }
}