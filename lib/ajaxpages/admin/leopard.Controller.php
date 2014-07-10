<?php
/**
 * Leopard - package management
 *
 * @package Panthera\core\system\leopard
 * @author Damian Kęska
 * @license LGPLv3
 */
 
/**
 * Leopard - package management
 *
 * @package Panthera\core\system\leopard
 * @author Damian Kęska
 */

class leopardAjaxControllerSystem extends pageController
{
    /**
     * Required modules
     * 
     * @var $requirements
     */
    
    protected $requirements = array(
        'leopard',
        'filesystem',
        'scm',
    );
    
    /**
     * Default page title (uiTitlebar integration)
     * 
     * @var $uiTitlebar
     */
    
    protected $uiTitlebar = array(
        'Packages management', 'leopard',
    );
    
    /**
     * User permissions
     * 
     * @var $permissions
     */
    
    protected $permissions = array(
        'admin.packages.management' => array('Packages management', 'leopard'),
    );
    
    /**
     * Default action - display
     * 
     * @author Damian Kęska
     * @return string
     */
    
    public function display()
    {
        $this -> panthera -> logging -> clear();
        $this -> panthera -> logging -> filter = array(
            'leopard' => True,
            'scm' => True,
        );
        $this -> panthera -> logging -> filterMode = 'whitelist';
        
        $this -> dispatchAction();
        
        if($this -> session -> exists('leopard.build.last'))
        {
            $last = $this -> session -> get('leopard.build.last');
            $this -> template -> push('buildName', $last[0]);
            $this -> template -> push('buildPath', $last[1]);
        
            if ($last[2])
                $this -> template -> push('buildBranch', $last[2]);
        
            $this -> template -> push('buildMode', $last[3]);
        }
        
        $this -> template -> push (array(
            'SITE_DIR' => SITE_DIR,
            'installedPackages' => leopard::getInstalledPackages(),
            'consoleOutput' => nl2br($this -> panthera -> logging -> getOutput()),
        ));
        
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/package.png', 'left');
        return $this -> template -> compile('leopard.tpl');
    }
    
    /**
     * Upload a package file
     * 
     * @input $_FILES['packageFile']
     * @author Damian Kęska
     * @return null
     */
    
    public function uploadAction()
    {
        if (isset($_FILES['packageFile']))
        {
            // delete old package file
            if ($this -> session -> exists('leopard.file'))
            {
                unlink($this -> session -> exists('leopard.file'));
                $this -> session -> remove('leopard.file');
            }
    
            $fileName = basename($_FILES['packageFile']['name']);
    
            move_uploaded_file($_FILES['packageFile']['tmp_name'], SITE_DIR. '/content/tmp/' .$fileName);
            $this -> session -> set('leopard.file', SITE_DIR. '/content/tmp/' .$fileName);
    
            try {
                $package = new leopardPackage(SITE_DIR. '/content/tmp/' .$fileName);
                $manifest = $package -> manifest();
                
            } catch (Exception $e) {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Got exception during package read', 'leopard'),
                    'log' => nl2br($this -> panthera -> logging -> getOutput()),
                ));
            }
    
            $this -> panthera -> logging -> output ("Files in archive: \n".implode("\n", $package->getFiles())."\n", 'leopard');
            
            ajax_exit(array(
                'status' => 'success',
                'name' => $package -> getName(),
                'version' => $manifest -> version,
                'release' => $manifest -> release,
                'author' => $manifest -> author,
                'description' => $manifest -> description,
                'website' => $manifest -> url,
                'installed' => leopard::checkInstalled($package -> getName()),
                'log' => nl2br($this -> panthera -> logging -> getOutput()),
            ));
            
        } else
            $this -> panthera -> logging -> output ('No input file received', 'leopard');
    
        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Unknown error'),
            'log' => nl2br($this -> panthera -> logging -> getOutput()),
        ));
    }

    /**
     * Install or remove package
     * 
     * @input $_POST['package']
     * @author Damian Kęska
     * @return null
     */

    public function manageAction()
    {
        $package = $_POST['package'];

        // TODO: Download package from repository
        if ($package == '_currentUploaded')
        {
            if (!$this -> session -> exists('leopard.file'))
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('No file stored in session cache', 'leopard'),
                ));
    
            $package = $this -> session -> get('leopard.file');
    
            if (!is_file($package))
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Package file no longer exists', 'leopard'),
                ));
        }
    
        if ($_POST['job'] == 'install')
            leopard::install($package);
        elseif ($_POST['job'] == 'uninstall')
            leopard::remove(leopard::packageName($package));
    
        ajax_exit(array(
            'status' => 'success',
            'log' => nl2br($this -> panthera -> logging -> getOutput()),
            'packages' => leopard::getInstalledPackages(),
        ));
    }

    /**
     * Download created package
     * 
     * @input $_GET['id'] Package build id
     * @author Damian Kęska
     * @return null
     */

    public function downloadCreatedPackageAction()
    {
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

    /**
     * Build a package file
     * 
     * @input $_POST['name'] Package name
     * @input $_POST['directory'] Package directory
     * @input $_POST['buildMode'] Build mode
     * @input $_POST['branch'] (Optional) Git branch
     * @return null
     * @author Damian Kęska
     */

    public function createAction()
    {
        $packageName = null;
        
        if (isset($_POST['name']))
            $packageName = strip_tags($_POST['name']);
        
        $packageDirectory = strip_tags($_POST['directory']);
        $packageID = md5($packageDirectory);
        $tmpDir = null;
        
        $this -> panthera -> logging -> output('Package directory: ' .$packageDirectory, 'leopard');
    
        // check if its a code repository
        if (!is_dir($packageDirectory))
        {
            $tmpDir = SITE_DIR. '/tmp/' .$packageID. '-scm';
    
            if (!scm::cloneBranch($packageDirectory, $tmpDir, $_POST['branch']))
            {
                $this -> panthera -> logging -> output('Not a code repository and not a directory, cannot find package sources', 'leopard');
                
                ajax_exit(array(
                    'status' => 'failed',
                    'log' => nl2br($this -> panthera -> logging -> getOutput()),
                ));
            }
    
            $packageDirectory = $tmpDir;
        }
    
        // check if directory exists
        if (!is_dir($packageDirectory))
        {
            $this -> panthera -> logging -> output('Cannot create package: Package directory does not exists', 'leopard');
            
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Package directory does not exists', 'leopard'),
                'log' => nl2br($this -> panthera -> logging -> getOutput()),
            ));
        }
    
        // check for manifest.json
        if (!is_file($packageDirectory. '/manifest.json'))
        {
            if ($tmpDir != null)
                filesystem::deleteDirectory($tmpDir); // clean up
    
            $this -> panthera -> logging -> output('Cannot create package: Cannot find mainfest.json in root directory of package', 'leopard');
            
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Cannot find mainfest.json in root directory of package', 'leopard'),
                'log' => nl2br($this -> panthera -> logging -> getOutput()),
            ));
        }
        
        $manifest = leopardPackage::readManifestFile($packageDirectory. '/manifest.json');
        
        if (isset($manifest -> name) and $manifest -> name)
            $packageName = $manifest -> name;
        
        $this -> panthera -> logging -> output('Package name: ' .$packageName, 'leopard');
    
        // check for installer hooks
        if (!is_file($packageDirectory. '/leopard.hooks.php'))
            $this -> panthera -> logging -> output('leopard.hooks.php does not exists, installation hooks will be disabled', 'leopard');
    
        try {
            $package = new leopardPackage(SITE_DIR. '/content/tmp/' .$packageID. '.phar');
            $package -> buildFromDirectory($packageDirectory);
            $package -> save();
            $this -> panthera -> logging -> output('Creating a package from "' .$packageDirectory. ' directory"', 'leopard');
            
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                'message' => $e->getMessage(),
                'log' => nl2br($this -> panthera -> logging -> getOutput()),
            ));
    
            if ($tmpDir != null)
                filesystem::deleteDirectory($tmpDir); // clean up
        }
    
        $url = '?display=leopard&cat=admin&action=downloadCreatedPackage&id=' .$packageID. '&fileName=' .$packageName. '-' .$package->manifest()->version. '-' .$package->manifest()->release. '.phar&_bypass_x_requested_with=True';
    
        $this -> panthera -> logging -> output ('url=' .$url, 'leopard');
    
        if ($tmpDir != null)
            filesystem::deleteDirectory($tmpDir); // clean up
    
        // save to session for future reuse
        $this -> session -> set('leopard.build.last', array(
            $packageName, $packageDirectory, $_POST['branch'], $_POST['buildMode'],
        ));
    
        if ($_POST['buildMode'] == 'install')
        {
            $url = False;
            leopard::install(SITE_DIR. '/content/tmp/' .$packageID. '.phar');
            
        } elseif ($_POST['buildMode'] == 'reinstall') {
            $url = False;
            leopard::remove($package -> manifest() -> name);
            leopard::install(SITE_DIR. '/content/tmp/' .$packageID. '.phar');
        }
    
        ajax_exit(array(
            'status' => 'success',
            'url' => $url,
            'log' => nl2br($this -> panthera -> logging -> getOutput()),
            'packages' => leopard::getInstalledPackages(),
        ));
    }
}
