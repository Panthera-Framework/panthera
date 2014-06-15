<?php
/**
 * Files download controller
 *
 * @package Panthera\core\upload
 * @author Damian Kęska
 * @license LGPLv3
 */

require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

/**
 * Files download controller
 *
 * @package Panthera\core\upload
 * @author Damian Kęska
 * @license LGPLv3
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
        $_GET['filename'] = urldecode($_GET['filename']);
        $error = null;

        if (!$this -> panthera -> user or !$this -> panthera -> user -> exists())
            $error = 'User not logged in';

        $file = new uploadedFile('id', $_GET['fileid']);

        if ($file -> filename != $_GET['filename'])
            $error = 'URL filename and upload filename do not match';

        if (!$file -> exists() or !$this -> checkPermissions('upload.file.dl.' .$_GET['fileid'], true))
            $error = 'File does not exists in upload or no upload permissions';

        // check if file exists
        if (!is_file($file -> getLocation()) or !is_readable($file -> getLocation()))
            $error = 'File does not exists or is not readable - "' .$file -> getLocation(). '"';

        $this -> getFeatureRef('front.download.status', $error, $file);

        if ($error)
        {
            $this -> panthera -> logging -> output($error, 'pantheraUpload');
            $this -> noAccess();
        }

        header('Content-Type: ' .$file -> mime);
        header('Content-Disposition: attachment; filename="' .$file -> filename. '"');
        print(file_get_contents($file -> getLocation()));
        exit;
    }

    /**
     * Display no access error
     *
     * @return null
     */

    public function noAccess()
    {
        pantheraCore::raiseError('notfound');
        exit;
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, 'downloadControllerSystem');