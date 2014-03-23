<?php

/**
 * Avatars management
 * Manager of avatars, uploaded images to `avatars` upload category
 *
 * @package Panthera\core\avatars\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
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


        // get upload and gallery objects
        list($avatarsUpload, $avatarsGallery) = $this -> checkAvatarsCategories();
        
        // get mime type
        $mime = filesystem::getFileMimeType($_FILES['input_file']['name']);
        
        // get set dimensions from database
        $d = $this->panthera->config->getKey('avatar_dimensions');
        $dimensions = explode('x', $d); 
        
        // get uploaded image as instance
        $image = new SimpleImage();
        $image -> load($_FILES['input_file']['tmp_name']);
        
        // check dimensions
        if ($image->getWidth() != intval($dimensions[0]) or $image->getHeight() != intval($dimensions[1]))
            ajax_exit(array('status' => 'failed', 'message' => localize('Dimensions are incorrect. It needs to be: ').$d));
        
        // upload file, add record to database
        $fileID = pantheraUpload::handleUpload($_FILES['input_file'], $avatarsUpload->name, $this->panthera->user->id, $this->panthera->user->login, 0, 0, $mime, '');
        
        $file = new uploadedFile('id', $fileID);
        
        // check if file exists in database
        if (!$file -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot handle file to avatars category.', 'avatars')));
        
        
        // create gallery item
        if (galleryItem::createGalleryItem('', '', pantheraUrl($file -> getLink(), True), $avatarsGallery->id, True, $file, $this->panthera->user->id, $this->panthera->user->login))
            ajax_exit(array('status' => 'success'));
        
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot add avatar to gallery category.', 'avatars')));
        
        
        pa_exit();
    }
    
    
    
    /**
     * Display all avatars, that user is able to use
     * 
     * @author Mateusz Warzyński
     * @return null
     */
     
    public function displayAvatarsAction()
    {
        // get avatars category (it is gallery category)
        $avatarsGallery = new galleryCategory('unique', 'avatars');
        
        // check if gallery category exists
        if (!$avatarsGallery -> exists())
        {
            list($upload, $gallery) = $this -> checkAvatarsCategories();
            
            if ($gallery -> exists())
                $avatarsGallery = $gallery;
            else
                ajax_exit(array('status' => 'failed', 'message' => localize('Avatars gallery category is not created!', 'avatars')));
        }
        
        // create query
        $by = new whereClause();
        $by -> add( 'AND', 'author_id', '=', $this->panthera->user->id);
        $by -> add( 'AND', 'gallery_id', '=', $avatarsGallery->id);
        
        // get avatars
        $items = galleryItem::getGalleryItems($by);
        
        // send items data to template
        $this -> panthera -> template -> push('avatars', $items);
        
        $this -> panthera -> template -> display('avatarsPopup.tpl');
        pa_exit();
    }
    
    
    
    /**
     * Get avatar categories objects (upload, category)
     * 
     * @author Mateusz Warzyński
     * @return array
     */
     
    protected function checkAvatarsCategories()
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
        }

        // get avatars gallery category
        $avatarsGallery = new galleryCategory('unique', 'avatars');
        
        if (!$avatarsGallery -> exists())
        {
            if (!gallery::createCategory('avatars', $this->panthera->user->login, $this->panthera->user->id, $this->panthera->locale->getActive(), False, $this->panthera->user->full_name, 'avatars'))
                ajax_exit(array('status' => 'failed', 'message' => localize('Error! Gallery category for avatars is not created.', 'avatars')));
            
            else
                $avatarsGallery = new galleryCategory('unique', 'avatars');
        }
        
        return array($avatarsUpload, $avatarsGallery);
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