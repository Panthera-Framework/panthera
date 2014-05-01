<?php
/**
 * Files download controller
 *
 * @package Panthera\core\upload
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

require_once 'content/app.php';
include getContentDir('pageController.class.php');

/**
 * Files download controller
 *
 * @package Panthera\core\upload
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

class downloadControllerSystem extends pageController
{
    /**
     * Main function
     * 
     * @return null
     */
    
    public function display()
    {
        if (!$this -> panthera -> user or !$this -> panthera -> user -> exists())
        {
            $this -> panthera -> logging -> output('User not logged in', 'pantheraUpload');
            $this -> noAccess();
        }
        
        $file = new uploadedFile('id', $_GET['fileid']);
        
        if ($file -> filename != $_GET['filename'])
        {
            $this -> panthera -> logging -> output('URL filename and upload filename do not match', 'pantheraUpload');
            $this -> noAccess();
        }
        
        if (!$file -> exists() or !$this -> checkPermissions('upload.file.dl.' .$_GET['fileid'], true))
            $this -> noAccess();

        // check if file exists
        if (!is_file($file -> getLocation()) or !is_readable($file -> getLocation()))
        {
            $this -> panthera -> logging -> output('File does not exists or is not readable - "' .$file -> getLocation(). '"', 'pantheraUpload');
            $this -> noAccess();
        }
        
        header('Content-Type: ' .$file -> mime);
        header('Content-Disposition: attachment; filename="' .$file -> filename. '"');
        print(file_get_contents($file -> getLocation()));
        exit;
    }
    
    public function noAccess()
    {
        $this -> panthera -> raiseError('404');
        exit;
    }
}

// if you want to copy this front controller to your site directory instead of linking please change PANTHERA_DIR to SITE_DIR inside of your copy or you can make a include
if (strpos(__FILE__, PANTHERA_DIR) !== FALSE)
{
    $object = new downloadControllerSystem();
    $object -> display();   
}