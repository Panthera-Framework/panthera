<?php
/**
  * Leopard - package management
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_manage_packages')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

// import module and locales
$panthera -> importModule('leopard');
$panthera -> locale -> loadDomain('leopard');

$panthera -> logging -> clear();
$panthera -> logging -> filter = array('leopard' => True, 'scm' => True);
$panthera -> logging -> filterMode = 'whitelist';

/**
  * Upload a package file
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'upload')
{
    $panthera -> importModule('filesystem');

    // flush buffers to automaticaly update console
    /*function bufferProgressOutput($msg)
    {
        print($msg. "\n");
        ob_flush();
    }

    $panthera -> add_option('logging.output', 'bufferProgressOutput');*/

    if (isset($_FILES['packageFile']))
    {
        if (filesize($_FILES['packageFile']['tmp_name']) > 102400 or filesize($_FILES['packageFile']['tmp_name']) < 256)
            ajax_exit(array('status' => 'failed', 'message' => slocalize('Uploaded file exceeds %s bytes limit or is empty', 'leopard', 102400)));
            
        // delete old package file
        if ($panthera -> session -> exists('leopard.file'))
        {
            unlink($panthera -> session -> exists('leopard.file'));
            $panthera -> session -> remove('leopard.file');
        }
            
        $fileName = basename($_FILES['packageFile']['name']);
        
        move_uploaded_file($_FILES['packageFile']['tmp_name'], SITE_DIR. '/content/tmp/' .$fileName);
        $panthera -> session -> set('leopard.file', SITE_DIR. '/content/tmp/' .$fileName);
        
        try {
            $package = new leopardPackage(SITE_DIR. '/content/tmp/' .$fileName);
            $manifest = $package->manifest();
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Got exception during package read', 'leopard'), 'log' => nl2br($panthera -> logging -> getOutput())));
        }
        
        $panthera -> logging -> output ("Files in archive: \n".implode("\n", $package->getFiles())."\n", 'leopard');
        
        ajax_exit(array('status' => 'success', 'name' => $package->getName(), 'version' => $manifest->version, 'release' => $manifest->release, 'author' => $manifest->author, 'description' => $manifest->description, 'website' => $manifest->url, 'installed' => leopard::checkInstalled($package->getName()), 'log' => nl2br($panthera -> logging -> getOutput())));
    } else {
        $panthera -> logging -> output ('No input file received', 'leopard');
    }
    
    ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error'), 'log' => nl2br($panthera -> logging -> getOutput())));
    
/**
  * Manage packages
  *
  * @author Damian Kęska
  */
  
} elseif ($_GET['action'] == 'manage') {

    /**
      * Installing and removing a package
      *
      * @author Damian Kęska
      */

    $package = $_POST['package'];
        
    // TODO: Download package from repository
        
    if ($package == '_currentUploaded')
    {
        if (!$panthera->session->exists('leopard.file'))
            ajax_exit(array('status' => 'failed', 'message' => localize('No file stored in session cache', 'leopard')));
                
        $package = $panthera->session->get('leopard.file');
            
        if (!is_file($package))
            ajax_exit(array('status' => 'failed', 'message' => localize('Package file no longer exists', 'leopard')));
    }
        
    if ($_POST['job'] == 'install')
        leopard::install($package);
    elseif ($_POST['job'] == 'uninstall')
        leopard::remove(leopard::packageName($package));
        
    ajax_exit(array('status' => 'success', 'log' => nl2br($panthera -> logging -> getOutput()), 'packages' => leopard::getInstalledPackages()));
    
/**
  * Build package from directory
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'create') {
    $packageName = strip_tags($_POST['name']);
    $packageDirectory = strip_tags($_POST['directory']);
    $packageID = md5($packageName.$packageDirectory);
    $tmpDir = null;
    
    $panthera -> logging -> output('Package name: ' .$packageName, 'leopard');
    
    // check if its a code repository
    if (!is_dir($packageDirectory))
    {
        $panthera -> importModule('scm'); // maybe its a git repository, lets check it out
        $panthera -> importModule('filesystem');
        
        $tmpDir = SITE_DIR. '/tmp/' .$packageID. '-scm';
        
        if (!scm::cloneBranch($packageDirectory, $tmpDir, $_POST['branch']))
        {
            $panthera -> logging -> output('Not a code repository and not a directory, cannot find package sources', 'leopard');
            ajax_exit(array('status' => 'failed', 'log' => nl2br($panthera -> logging -> getOutput())));
        }
        
        $packageDirectory = $tmpDir;
    }

    // check if directory exists
    if (!is_dir($packageDirectory))
    {
        $panthera -> logging -> output('Cannot create package: Package directory does not exists', 'leopard');
        ajax_exit(array('status' => 'failed', 'message' => localize('Package directory does not exists', 'leopard'), 'log' => nl2br($panthera -> logging -> getOutput())));
    }
    
    // check for manifest.json
    if (!is_file($packageDirectory. '/manifest.json'))
    {
        if ($tmpDir != null)
            deleteDirectory($tmpDir); // clean up
    
        $panthera -> logging -> output('Cannot create package: Cannot find mainfest.json in root directory of package', 'leopard');
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find mainfest.json in root directory of package', 'leopard'), 'log' => nl2br($panthera -> logging -> getOutput())));
    }
    
    // check for installer hooks
    if (!is_file($packageDirectory. '/leopard.hooks.php'))
    {
        $panthera -> logging -> output('leopard.hooks.php does not exists, installation hooks will be disabled', 'leopard');
    }
    
    try {
        $package = new leopardPackage(SITE_DIR. '/content/tmp/' .$packageID. '.phar');
        $package -> buildFromDirectory($packageDirectory);
        $package -> save();
        $panthera -> logging -> output('Creating a package from "' .$packageDirectory. ' directory"', 'leopard');
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => $e->getMessage(), 'log' => nl2br($panthera -> logging -> getOutput())));
        
        if ($tmpDir != null)
            deleteDirectory($tmpDir); // clean up
    
    }
    
    $url = '?display=leopard&cat=admin&action=downloadCreatedPackage&id=' .$packageID. '&fileName=' .$packageName. '-' .$package->manifest()->version. '-' .$package->manifest()->release. '.phar&_bypass_x_requested_with=True';
    
    $panthera -> logging -> output ('url=' .$url, 'leopard');
    
    if ($tmpDir != null)
        deleteDirectory($tmpDir); // clean up
    
    // save to session for future reuse
    $panthera->session->set('leopard.build.last', array($packageName, $packageDirectory, $_POST['branch']));
    ajax_exit(array('status' => 'success', 'url' => $url, 'log' => nl2br($panthera -> logging -> getOutput())));
   
/**
  * Download built package file
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'downloadCreatedPackage') {

    $id = str_replace('..', '', addslashes($_GET['id']));
    
    if (is_file(SITE_DIR. '/content/tmp/' .$id. '.phar'))
    {
        if (strlen($_GET['fileName']) > 0)
            $fileName = $_GET['fileName'];
        else {
            $package = new leopardPackage(SITE_DIR. '/content/tmp/' .$id. '.phar');
            $meta = $package->manifest();
            $fileName = $package->getName(). '-' .$meta->version. '-' .$meta->release. '.phar';
        }
    
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' .$fileName. '"');
        print(file_get_contents(SITE_DIR. '/content/tmp/' .$id. '.phar'));
        unlink(SITE_DIR. '/content/tmp/' .$id. '.phar');
        pa_exit();
    }

}

// show page

if($panthera->session->exists('leopard.build.last'))
{
    $last = $panthera->session->get('leopard.build.last');
    $panthera -> template -> push('buildName', $last[0]);
    $panthera -> template -> push('buildPath', $last[1]);
    
    if ($last[2] != '')
        $panthera -> template -> push('buildBranch', $last[2]);
}

$panthera -> template -> push ('SITE_DIR', SITE_DIR);
$panthera -> template -> push ('installedPackages', leopard::getInstalledPackages());
$panthera -> template -> push ('consoleOutput', nl2br($panthera -> logging -> getOutput()));
$panthera -> template -> display('leopard.tpl');
pa_exit();
