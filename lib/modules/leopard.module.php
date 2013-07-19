<?php
/**
  * Simple package manager for Panthera Framework
  *
  * @package Panthera\modules\leopard
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
 
/**
  * Panthera package creator
  *
  * @package Panthera\modules\leopard
  * @author Damian Kęska
  */
  
class leopardPackage
{
    protected $manifest = array ('title' => '', 'description' => '', 'author' => '', 'contact' => array(), 'url' => '', 'files' => array(), 'version' => 0.1, 'release' => 1);
    public $phar;
    protected $destination;
    
    /**
      * Constructor
      *
      * @param string $destination Phar file
      * @author Damian Kęska
      */
    
    public function __construct($destination)
    {
        $this->destination = $destination;
        $import = False;
    
        if (is_file($this->destination))
            $import = True;

        $this->phar = new Phar($destination);
        $this->phar->startBuffering();
        
        if ($import == True)
        {
            try {
                $this -> manifest = json_decode(file_get_contents($this->phar['manifest.json']->getPathName()), True);
            } catch (Exception $e) { }
        }
        
        // TODO: Manifest integrity check
        
        if (!is_array($this->manifest))
            throw new Exception('Manifest is not an array after deserialization, check your JSON code');
    }
    
    /**
      * Provides read only manifest access
      *
      * @return object
      * @author Damian Kęska
      */
    
    public function manifest()
    {
        return (object)$this->manifest;
    }

    /**
      * Set package title
      *
      * @param string $title
      * @return bool
      * @author Damian Kęska
      */

    public function setTitle($title)
    {
        if (strlen($title) < 3 or strlen($title) > 64)
            throw new Exception('The title can be only 3-64 characters size');
            
        $this -> manifest['title'] = strip_tags($title);
        return True;
    }
    
    /**
      * Package description
      *
      * @param string $description
      * @return bool
      * @author Damian Kęska
      */
    
    public function setDescription($description)
    {
        if (strlen($description) < 3 or strlen($description) > 2048)
            throw new Exception('Description cant be shorter than 3 characters and longer than 2048 characters');
            
        $this -> manifest['description'] = strip_tags($description);
        return True;
    }
    
    /**
      * Package author
      *
      * @param string $author
      * @return bool
      * @author Damian Kęska
      */
    
    public function setAuthor($author)
    {
        if (strlen($author) < 3 or strlen($author) > 64)
            throw new Exception('Author name cant be shorter than 3 characters and longer than 64 characters');
            
        $this -> manifest['author'] = strip_tags($author);
        return True;
    }
    
    /**
      * Add contact informations
      *
      * @param string $contact
      * @param string $type eg. jabber, e-mail, gadu-gadu, aim, yahoo, msn or skype
      * @return bool 
      * @author Damian Kęska
      */
    
    public function addContact($contact, $type)
    {
        $contactTypes = array('jabber', 'e-mail', 'gadu-gadu', 'aim', 'yahoo', 'msn', 'skype');
        
        if (!in_array($type, $contactTypes))
            throw new Exception('Invalid contact type');
            
        if (strlen($contact) < 3 or strlen($contact) > 64)
            throw new Exception('Contact address cant be shorter than 3 characters and longer than 64 characters');
            
        if (!is_array($this->manifest['contact']))
            $this->manifest['contact'] = array();
            
        $this->manifest['contact'][] = array(strip_tags($contact), $type);
        return True;
    }
    
    /**
      * Set project's website
      *
      * @param string $url
      * @return bool 
      * @author Damian Kęska
      */
    
    public function setWebsite($url)
    {
        if(!filter_var($url, FILTER_VALIDATE_URL))
            throw new Exception('Invalid URL address specified');
            
        $this->manifest['url'] = $url;
        return True;
    }

    /**
      * Set package version
      *
      * @param float $version
      * @param int $release
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function setVersion($version, $release=1)
    {
        if (!is_float($version))
            throw new Exception('Input version must be a float number eg. 1.0, not 1');
            
        if (!is_int($release) or is_float($release))
            throw new Exception('Release number must be an integer, and cannot be a float');
            
        $this->manifest['version'] = $version;
        $this->manifest['release'] = $release;
        return True;
    }
    
    /**
      * Import ready to use manifest file
      *
      * @param string $file Path to manifest.json file
      * @return bool 
      * @author Damian Kęska
      */
    
    public function importManifest($file)
    {
        if(!is_file($file))
            throw new Exception('Cannot find manifest file "' .$file. '"');
            
        $json = json_decode(file_get_contents($file), True);
        
        if ($json)
        {
            $this->manifest = $json;
            return True;
        }
        
        return False;
    }
    
    /**
      * Export manifest to file
      *
      * @param string $file Path where to save manifest.json file
      * @return bool 
      * @author Damian Kęska
      */
    
    public function exportManifest($file)
    {
        $fp = @fopen($file, 'w');
        @fwrite($fp, json_encode($this->manifest, JSON_PRETTY_PRINT));
        @fclose($fp);
        
        if (is_file($file))
            return True;
        
        return False;
    }
    
    /**
      * Add a file to package archive
      *
      * @param string $local Path to file in local filesystem
      * @param string $inArchive Destination path in archive
      * @return bool 
      * @author Damian Kęska
      */
    
    public function addFile($local, $inArchive)
    {
        if(!is_file($local))
            throw new Exception('Cannot open "' .$local. ' in read mode"');
            
        if ($inArchive[0] == '/' or $inArchive[0] == '.')
            throw new Exception('Slashes and dots are not allowed at beigning destination path in archive');
    
        $this->phar->addFile($local, $inArchive);
        $this->manifest['files'][$inArchive] = md5(file_get_contents($local));
        return True;
    }
    
    /**
      * Make empty directory inside of archive
      *
      * @param string $dir Path to directory
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function mkdir($dir)
    {
        try {
            $this->phar->addEmptyDir($dir);
            return True;        
        } catch (Exception $e) {
            return False;
        }
    }
    
    /**
      * Write manifest and save to file
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function save()
    {
        $this->phar->stopBuffering();
        $manifest = json_encode($this->manifest, JSON_PRETTY_PRINT);
        $this->phar->addFromString('manifest.json', $manifest);
    }
    
    /**
      * Get complete list of files inside archive
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function getFiles()
    {
        $pharRoot = 'phar://' .realpath($this->destination). '/';
        $files = array();
        $ph = new Phar($this->destination);
    
        foreach (new RecursiveIteratorIterator($ph) as $key => $file)
        {
            $filePath = str_replace($pharRoot, '', $key);
            $files[] = $filePath;
        }
        
        unset($ph);
        return $files;
    }
}

/**
  * Panthera package manager
  *
  * @package Panthera\modules\leopard
  * @author Damian Kęska
  */

class leopard
{
    protected static $database = null;
    
    /**
      * Copy MySQL database to memory for faster and easier access
      *
      * @return void 
      * @author Damian Kęska
      */

    protected static function rebuildDB()
    {
        global $panthera;
        self::$database = array();
        
        // selecting all packages from database
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}leopard_packages`');
        $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);
        
        $panthera -> logging -> output ('Read ' .$SQL->rowCount(). ' packages from database', 'leopard');
        
        foreach ($fetch as $row)
        {
            self::$database[$row['name']] = array('info' => $row, 'files' => array());
        }
        
        // updating records with files meta
        $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}leopard_files`');
        $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($fetch as $row)
        {
            self::$database[$row['package']]['files'][$row['path']] = $row;
        } 
        
        $panthera -> logging -> output ('Found ' .$SQL->rowCount(). ' managed files', 'leopard');
    }


    /**
      * Check if package is installed
      *
      * @param string $package name
      * @return bool
      * @author Damian Kęska
      */

    public static function checkInstalled($package)
    {
        if (self::$database == null)
            self::rebuildDB();

        if (array_key_exists($package, self::$database))
            return True;
        
        return False;
    }
    
    /**
      * Get package informations
      *
      * @param string $package name
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function getInstalled($package)
    {
        if(self::checkInstalled($package))
            return (object) self::$database[$package];
        
        return False;
    }
    
    /**
      * Convert package path to package name
      *
      * @param string $inputPath
      * @return string 
      * @author Damian Kęska
      */
    
    public static function packageName($inputPath)
    {
        $pathinfo = pathinfo($inputPath);
        return strtolower($pathinfo['filename']); // TODO: Support for names eg. package-1.0-2 or package-1.0
    }
    
    /**
      * Pre installation check
      *
      * @param string $packageFile Path to package file
      * @param bool $overwriteFS Allow overwriting files on filesystem
      * @param bool $overwritePKGS Allow overwriting files that belongs to already installed package (can be dangerous)
      * @return mixed 
      * @author Damian Kęska
      */

    protected static function preInstallCheck($packageName, $package, $overwriteFS=True, $overwritePKGS=False)
    {
        global $panthera;

        $packageMeta = $package -> manifest();
        
        if (self::checkInstalled($packageName))
        {
            $installed = self::getInstalled($packageName);
        } else {
            // TODO: Dependency support
        
            // check file collisions
            foreach ((array)$packageMeta->files as $file => $sum)
            {
                if (is_file(SITE_DIR. '/' .$file))
                {
                    $panthera -> logging -> output ('Warning: ' .SITE_DIR. '/' .$file. ' is already in filesystem', 'leopard');
                    
                    $SQL = $panthera -> db -> query('SELECT `package` FROM `{$db_prefix}leopard_files` WHERE `path` = :path', array('path' => $file));
                    
                    if ($SQL -> rowCount() > 0)
                    {
                        $fetch = $SQL->fetch(PDO::FETCH_ASSOC);
                        $panthera -> logging -> output('Warning: ' .SITE_DIR. '/' .$file. ' already belongs to ' .$fetch['package']. ' but ' .$packageFile. ' is providing it too', 'leopard');
                        
                        if ($overwritePKGS == False)
                        {
                            $panthera -> logging -> output ('preInstallCheck failed, cannot overwrite files that already belongs to other package', 'leopard');
                            return False;
                        }
                    }

                    if ($overwriteFS == False)
                    {
                        $panthera -> logging -> output ('preInstallCheck failed, cannot overwrite files that already exists in filesystem', 'leopard');
                        return False;
                    }
                }
            }
            
            return True;
        }
    }
    
    /**
      * Install package from phar archive
      *
      * @param string $packageFile
      * @param bool $overwriteFS Overwrite existing files in filesystem
      * @param bool $overwritePKGS Overwrite existing files that belongs to already installed packages
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function install($packageFile, $overwriteFS=True, $overwritePKGS=False)
    {
        global $panthera;
        
        // TODO: Panthera run locks checking
        
        $packageName = self::packageName($packageFile);
        
        $panthera -> logging -> output ('Preparing to install package "' .$packageName. '" ("' .$packageFile. '")', 'leopard');
        
        // reading package contents        
        try {
            $package = new leopardPackage($packageFile);
        } catch (Exception $e) {
            $panthera -> logging -> output ('Cannot read package, got exception - ' .$e -> getMessage(), 'leopard');
            return False;
        }
    
        // pre installation check
        if (!self::preInstallCheck($packageName, $package, $overwriteFS, $overwritePKGS))
        {
            $panthera -> logging -> output ('Pre-installation check failed, check previous messages', 'leopard');
            return False;
        }
        
        try {
            $package -> phar['leopard.hooks.php'];
            include $package -> phar['leopard.hooks.php'];
        } catch (Exception $e) {
            // pass
            $panthera -> logging -> output('leopard.hooks.php file not found in archive root or other error occured, exception: ' .$e->getMessage(), 'leopard');
        }
        
        // pre-installation hooks
        $panthera -> logging -> output ('Running pre-installation hooks', 'leopard');
        $package = $panthera -> get_filters('leopard.preinstall', $package);
        $panthera -> get_options('leopard.preinstall.' .$packageName, '');
        
        // TODO: Dependency support
        
        $packageMeta = $package -> manifest();
        
        // create package record
        $array = array('name' => $packageName, 'manifest' => file_get_contents($package -> phar['manifest.json']), 'installed_as' => 'manual', 'version' => $packageMeta->version, 'release' => $packageMeta->release, 'status' => 'broken');
        $panthera -> db -> query('INSERT INTO `{$db_prefix}leopard_packages` (`id`, `name`, `manifest`, `installed_as`, `version`, `release`, `status`) VALUES (NULL, :name, :manifest, :installed_as, :version, :release, :status)', $array);
        
        // create backup directory
        $backupDir = SITE_DIR. '/content/packages/backups/' .$packageName. '-' .$packageMeta->version. '-' .$packageMeta->release. '-backup';
        $panthera -> logging -> output ('Creating backup directory "' .$backupDir. '"', 'leopard');
        $dontBackup = False;
        
        if (!is_dir(SITE_DIR. '/content/packages/backups/'))
            @mkdir(SITE_DIR. '/content/packages/backups/');
            
        if (!is_dir($backupDir))
            @mkdir($backupDir);
        else
            $dontBackup = True; // dont overwrite old backup after package reinstall, etc.
            
        // installing files
        foreach ((array)$packageMeta->files as $file => $sum)
        {
            if ($file == 'leopard.hooks.php')
                continue;
        
            $panthera -> logging -> output('Installing "' .$file. '"', 'leopard');
            $contents = file_get_contents($package->phar[$file]->getPathName());
            
            // first make a backup of file that will be overwritten
            if (is_file(SITE_DIR. '/' .$file) and $dontBackup == False)
            {
                $panthera -> logging -> output ('Copying original file to backup "' .SITE_DIR. '/' .$file. '" -> "' .$backupDir. '/' .md5($file). '"', 'leopard');
                copy(SITE_DIR. '/' .$file, $backupDir. '/' .md5($file));
            }
            
            $fp = fopen(SITE_DIR. '/' .$file, 'w');
            fwrite($fp, $contents);
            fclose($fp);
            
            $array = array('path' => $file, 'sum' => md5($contents), 'package' => $packageName, 'dependencies' => serialize(array()));
            $panthera -> db -> query('INSERT INTO `{$db_prefix}leopard_files` (`id`, `path`, `md5`, `package`, `created`, `dependencies`) VALUES (NULL, :path, :sum, :package, NOW(), :dependencies)', $array);
        }

        // finish        
        $panthera -> db -> query('UPDATE `{$db_prefix}leopard_packages` SET `status` = "installed" WHERE `name` = :name', array('name' => $packageName));
        
        // post-installation hooks
        $panthera -> logging -> output ('Running post-installation hooks', 'leopard');
        $panthera -> get_options('leopard.postinstall', $packageName);
        $panthera -> get_options('leopard.postinstall.' .$packageName, '');
        
        // rebuild local database
        $panthera -> logging -> output ('Rebuiliding local database', 'leopard');
        self::rebuildDB();
        
        $panthera -> logging -> output ('Package "' .$packageName. '" installed', 'leopard');
        return True;
    }
    
    /**
      * Remove a package
      *
      * @param string $packageName
      * @param bool $dontRestoreBackup
      * @return bool 
      * @author Damian Kęska
      */

    public static function remove($packageName, $dontRestoreBackup=False)
    {
        global $panthera;
    
        // TODO: Dependency support and option to remove only single package without its dependencies
        
        $panthera -> logging -> output('Preparing to remove "' .$packageName. '" package', 'leopard');
    
        if (!self::checkInstalled($packageName))
        {
            $panthera -> logging -> output ('Package is not installed, nothing to remove', 'leopard');
            return False; // package is not installed
        }
        
        $package = self::getInstalled($packageName);
        $packageMeta = (object)$package -> info;
        
        $backupDir = SITE_DIR. '/content/packages/backups/' .$packageName. '-' .$packageMeta->version. '-' .$packageMeta->release. '-backup';
        
        foreach ((array)$package->files as $file => $sum)
        {
            $panthera -> logging -> output('Removing "' .$file. '" file from filesystem', 'leopard');
            unlink($file);
            
            if ($dontRestoreBackup == False)
            {
                $sum = md5($file);
            
                if (is_file($backupDir. '/' .$sum))
                    copy($backupDir. '/' .$sum, $file);
            }
        }

        // clean up
        $panthera -> logging -> output ('Cleaning up backup directory', 'leopard'); 
        $panthera -> importModule('filesystem');
        deleteDirectory($backupDir);
        
        // remove from database
        $panthera -> db -> query ('DELETE FROM `{$db_prefix}leopard_files` WHERE `package` = :packageName', array('packageName' => $packageName));
        $panthera -> db -> query ('DELETE FROM `{$db_prefix}leopard_packages` WHERE `name` = :packageName', array('packageName' => $packageName));
        
        // updating local database
        $panthera -> logging -> output ('Rebuiliding local database', 'leopard');
        self::rebuildDB();
        
        $panthera -> logging -> output('Package removed', 'leopard');
        return True;
    }
}
