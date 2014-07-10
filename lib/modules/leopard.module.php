<?php
/**
 * Simple package manager for Panthera Framework
 *
 * @package Panthera\core\system\leopard
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Panthera package edit class
 *
 * @package Panthera\core\system\leopard
 * @author Damian Kęska
 */

class leopardPackage
{
    protected $manifest = array();
    public $phar;
    protected $destination;
    protected $packageName = "";
    protected $panthera;

    /**
     * Constructor
     *
     * @param string $destination Phar file
     * @author Damian Kęska
     */

    public function __construct($destination)
    {
        $panthera = pantheraCore::getInstance();
        $this -> panthera = $panthera;

        // default manifest
        $this->manifest = array (
            'name' => '',
            'title' => '',
            'description' => '',
            'author' => '',
            'contact' => array(),
            'url' => '',
            'files' => array(),
            'version' => 0.1,
            'release' => 1,
            'dependencies' => array(),
            'ignoredfiles' => array(),
            'requirements' => array(
                'dependencies' => array(),
                'environment' => array()
            )
        );

        $this -> destination = $destination;
        $this -> packageName = leopard::packageName($this->destination);
        $import = False;

        if (is_file($this -> destination))
            $import = True;

        $panthera -> logging -> output ('Opening "' .$destination. '" file and starting buffering mode', 'leopard');

        $this -> phar = new Phar($destination);
        $this -> phar -> startBuffering();

        if ($import == True)
        {
            try {
                $this -> manifest = json_decode(file_get_contents($this -> phar['manifest.json'] -> getPathName()), True);
            } catch (Exception $e) {
                $panthera -> logging -> output('Cannot open manifest.json file', 'leopard');
            }
        }

        if (!isset($this->manifest['name']) and !$this -> manifest['name'])
            $this -> manifest['name'] = $this->packageName;
        
        $this -> packageName = $this -> manifest['name'];

        // TODO: Manifest integrity check

        if (!is_array($this -> manifest))
            throw new leopardException('Manifest is not an array after deserialization, check your JSON code');
    }

    /**
     * Provides read only manifest access
     *
     * @return object
     * @author Damian Kęska
     */

    public function manifest()
    {
        return (object)$this -> manifest;
    }
    
    /**
     * Read manifest file and return as array object
     * 
     * @param string $path Path to manifest.json
     * @return stdObject
     */
    
    public static function readManifestFile($path)
    {
        if (is_dir($path))
            $path .= '/manifest.json';
        
        if (is_file($path))
            return json_decode(file_get_contents($path));

        return false;
    }

    /**
     * Add file to ignore list
     *
     * @param string $fileName
     * @return bool
     * @author Damian Kęska
     */

    public function addIgnoredFile($fileName)
    {
        if ($fileName[0] == '.' or $fileName[0] == '/')
            throw new leopardException('Slashes and dots are not allowed at beigning destination path in archive');

        if (in_array($fileName, $this -> manifest['ignoredfiles']))
            return True;

        $this -> manifest['ignoredfiles'][] = $fileName;

        return True;
    }

    /**
     * Set package name
     *
     * @param string $name
     * @return bool
     * @author Damian Kęska
     */

    public function setPackageName($name)
    {
        $name = str_replace('-', '', $name);

        $this->manifest['name'] = leopard::packageName($name);
        return True;
    }

    /**
     * Set requirement, eg. Panthera or PHP version, required PHP modules
     *
     * @param string $requirement
     * @param string $value
     * @return bool
     * @author Damian Kęska
     */

    public function setRequire($requirement, $value)
    {
        $requirementsList = array(
            'PHP', 
            'Panthera', 
            'Template_Engine', 
            'PHP_Modules', 
            'Database_Driver',
        );

        if (!in_array($requirement, $requirementsList))
            throw new leopardException('Unsupported requirement "' .$requirement. ', not in list: ' .implode(', ', $requirementsList). '"');

        $this -> manifest['requirements']['environment'][$requirement] = $value;
        return True;
    }

    /**
     * Add required package as dependency
     *
     * @param string $package name
     * @param string $version
     * @return bool
     * @author Damian Kęska
     */

    public function addRequiredPackage($package, $version)
    {
        $this -> manifest['requirements']['dependencies'][$package] = $version;
        return True;
    }

    /**
     * Get package name
     *
     * @return string
     * @author Damian Kęska
     */

    public function getName()
    {
        return $this -> packageName;
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
            throw new leopardException('The title can be only 3-64 characters size');

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
            throw new leopardException('Description cant be shorter than 3 characters and longer than 2048 characters');

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
            throw new leopardException('Author name cant be shorter than 3 characters and longer than 64 characters');

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
        $contactTypes = array(
            'jabber',
            'e-mail',
            'gadu-gadu',
            'aim',
            'yahoo',
            'msn',
            'skype',
        );

        if (!in_array($type, $contactTypes))
            throw new leopardException('Invalid contact type');

        if (strlen($contact) < 3 or strlen($contact) > 64)
            throw new leopardException('Contact address cant be shorter than 3 characters and longer than 64 characters');

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
            throw new leopardException('Invalid URL address specified');

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
            throw new leopardException('Input version must be a float number eg. 1.0, not 1');

        if (!is_int($release) or is_float($release))
            throw new leopardException('Release number must be an integer, and cannot be a float');

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
            throw new leopardException('Cannot find manifest file "' .$file. '"');

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
        if (version_compare(phpversion(), '5.4.0', '>'))
            @fwrite($fp, json_encode($this->manifest, JSON_PRETTY_PRINT));
        else
            @fwrite($fp, json_encode($this->manifest));

        @fclose($fp);

        if (is_file($file))
            return True;

        return False;
    }

    /**
     * Show JSON formatted manifest file
     *
     * @return string
     * @author Damian Kęska
     */

    public function showManifest()
    {
        if (version_compare(phpversion(), '5.4.0', '>'))
            return json_encode($this->manifest, JSON_PRETTY_PRINT);
        else
            return json_encode($this->manifest);
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
            throw new leopardException('Cannot open "' .$local. '" in read mode"');

        if (in_array($inArchive, $this->manifest['ignoredfiles']))
        {
            $this -> panthera -> logging -> output('Ignoring file "' .$inArchive. '"', 'leopard');
            return False;
        }

        if ($inArchive[0] == '/' or $inArchive[0] == '.')
            throw new leopardException('Slashes and dots are not allowed at beigning destination path in archive');

        if (strtolower($inArchive) == 'manifest.json')
            return False;

        $this->phar->addFile($local, $inArchive);
        $this->manifest['files'][$inArchive] = md5(file_get_contents($local));
        return True;
    }

    /**
     * Build a package from directory
     *
     * @param string $directory
     * @return bool
     * @author Damian Kęska
     */

    public function buildFromDirectory($directory)
    {
        $panthera = pantheraCore::getInstance();

        if (!is_dir($directory))
        {
            $panthera -> logging -> output ('Cannot open directory "' .$directory. '"', 'leopard');
            return False;
        }

        $panthera -> importModule('filesystem');

        $panthera -> logging -> output ('Building package from directory "' .$directory. '"', 'leopard');

        // import manifest file if present
        if (is_file($directory. '/manifest.json'))
        {
            $panthera -> logging -> output ('Adding manifest.json from "' .$directory. '/manifest.json"', 'leopard');
            $this->importManifest($directory. '/manifest.json');
        }

        // leopard.hooks.php
        if (!is_file($directory. '/leopard.hooks.php'))
            $panthera -> logging -> output ('leopard.hooks.php file not found in build directory, installation hooks will not be avaliable', 'leopard');


        // add all other files
        $elements = filesystem::scandirDeeply($directory);

        foreach ($elements as $element)
        {
            $elementName = substr($element, (strlen($directory)+1), strlen($element));

            // skip some files
            if (strtolower($elementName) == 'manifest.json' or strtolower($elementName) == 'readme' or strtolower($elementName) == 'readme.md' or strtolower($elementName) == '.gitignore' or substr($elementName, 0, 4) == '.git')
                continue;

            // make sure the name will be correct
            if (strtolower($elementName) == 'leopard.hooks.php')
                $elementName = 'leopard.hooks.php';

            $panthera -> logging -> output ('Adding element "' .$elementName. '" to archive', 'leopard');

            if (is_dir($element))
                $this->mkdir($elementName);
            else
                $this->addFile($element, $elementName);
        }

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
     * Remove file from package
     *
     * @param string $inArchive path to file
     * @return bool
     * @author Damian Kęska
     */

    public function removeFile($inArchive)
    {
        try {
            $this->phar->delete($inArchive);
        } catch (Exception $e) {
            // pass
        }

        if ($this->fileExists($inArchive))
        {
            unset($this->manifest['files'][$inArchive]);
            return True;
        }

        return False;
    }

    /**
     * Check if file exists in archive
     *
     * @param string $inArchive path to file
     * @return mixed
     * @author Damian Kęska
     */

    public function fileExists($inArchive)
    {
        return array_key_exists($inArchive, $this->manifest['files']);
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

        if (version_compare(phpversion(), '5.4.0', '>'))
            $manifest = json_encode($this->manifest, JSON_PRETTY_PRINT);
        else
            $manifest = json_encode($this->manifest);

        $this->phar->addFromString('manifest.json', $manifest);
    }

    /**
     * Get complete list of files inside archive
     *
     * @param bool $listAllFiles List all files in archive
     * @return array
     * @author Damian Kęska
     */

    public function getFiles($listAllFiles=False)
    {
        if ($listAllFiles == True)
        {
            $pharRoot = 'phar://' .realpath($this->destination). '/';
            $files = array();
            $ph = new Phar($this->destination);

            foreach (new RecursiveIteratorIterator($ph) as $key => $file)
                $files[] = str_replace($pharRoot, '', $key);

            unset($ph);
            return $files;
        } else {
            $files = array();
            
            foreach ($this->manifest['files'] as $path => $file)
                $files[] = $path;
            
            return $files;
        }
    }

    /**
     * Update files inside of archive
     *
     * @param string $dir Root directory where source files are
     * @return bool
     * @author Damian Kęska
     */

    public function updateFiles($dir='')
    {
        $files = $this -> getFiles(True);

        if ($dir == '')
            $dir = dirname($this->destination);

        foreach ($files as $file)
        {
            if (is_file($dir. '/' .$file))
                $this -> addFile($dir. '/' .$file, $file);
        }

        return True;
    }
}

/**
 * Panthera package manager
 *
 * @package Panthera\core\system\leopard
 * @author Damian Kęska
 */

class leopard
{
    protected static $database = null;

    /**
     * Copy SQL database to memory for faster and easier access
     *
     * @return void
     * @author Damian Kęska
     */

    protected static function rebuildDB()
    {
        $panthera = pantheraCore::getInstance();
        self::$database = array();

        // selecting all packages from database
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}leopard_packages`');
        $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);

        $fetch = $panthera -> get_filters('leopard.rebuilddb.packages', $fetch);

        $panthera -> logging -> output ('Read ' .count($fetch). ' packages from database', 'leopard');

        foreach ($fetch as $row)
            self::$database[$row['name']] = array(
                'info' => $row,
                'files' => array(),
            );

        if (count(self::$database) > 0)
        {
            // updating records with files meta
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}leopard_files`');
            $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);

            foreach ($fetch as $row)
                self::$database[$row['package']]['files'][$row['path']] = $row;

            $panthera -> logging -> output ('Found ' .count($fetch). ' managed files', 'leopard');
        }
    }

    /**
     * Compare two package versions
     *
     * @param string $first package
     * @param string $second package
     * @return bool True if first package is newer than second package
     * @author Damian Kęska
     */

    public static function compareVersions($first, $second)
    {
        $checkFirst = self::packageName($first, True);
        $checkSecond = self::packageName($second, True);

        // convert package name to only version
        if (!is_numeric($checkFirst[1]))
            $first = str_replace($checkFirst[1]. '-', '', $first);

        if (!is_numeric($checkSecond[1]))
            $second = str_replace($checkSecond[1]. '-', '', $second);

        // example of input: 1.1-1
        $first = intval(str_replace('-', '', str_replace('.', '', $first)));
        $second = intval(str_replace('-', '', str_replace('.', '', $second)));

        if ($first > $second)
            return True;

        return False;
    }

    /**
     * Get list of installed packages
     *
     * @return array
     * @author Damian Kęska
     */

    public static function getInstalledPackages()
    {
        if (self::$database == null)
            self::rebuildDB();

        return self::$database;
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

        $package = self::packageName($package);

        if (isset(self::$database[$package]))
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
     * @param bool $returnMatches
     * @return string
     * @author Damian Kęska
     */

    public static function packageName($inputPath, $returnMatches=False)
    {
        $pathinfo = pathinfo(strtolower($inputPath));

        preg_match('/^([A-Za-z0-9_]+)\-?([0-9.]+)?\-?([0-9]+)?/', strtolower($pathinfo['filename']), $matches);

        if ($returnMatches == False)
            return $matches[1];

        return $matches;
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
        $panthera = pantheraCore::getInstance();

        $packageMeta = $package -> manifest();

        if ($packageMeta->name)
            $packageName = $packageMeta->name;

        $packageName = strtolower($packageName);

        if (self::checkInstalled($packageName))
        {
            // TODO: Package upgrades
            $panthera -> logging -> output ('Package is already installed', 'leopard');
            return False;
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

                        if (!$overwritePKGS)
                        {
                            $panthera -> logging -> output ('preInstallCheck failed, cannot overwrite files that already belongs to other package', 'leopard');
                            return False;
                        }
                    }

                    if (!$overwriteFS)
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
        $panthera = pantheraCore::getInstance();

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

        $packageMeta = $package -> manifest();

        if ($packageMeta -> name)
            $packageName = $packageMeta -> name;

        $packageName = strtolower($packageName);

        // pre installation check
        if (!self::preInstallCheck($packageName, $package, $overwriteFS, $overwritePKGS))
        {
            $panthera -> logging -> output ('Pre-installation check failed, check previous messages', 'leopard');
            return False;
        }

        try {
            $package -> phar['leopard.hooks.php'];
            include_once $package -> phar['leopard.hooks.php'];
            
        } catch (Exception $e) {
            // pass
            $panthera -> logging -> output('leopard.hooks.php file not found in archive root or other error occured, exception: ' .$e->getMessage(), 'leopard');
        }

        // pre-installation hooks
        $panthera -> logging -> output ('Running pre-installation hooks', 'leopard');
        $package = $panthera -> get_filters('leopard.preinstall', $package);
        $panthera -> get_options('leopard.preinstall.' .$packageName, '');

        // TODO: Dependency support

        // create package record
        $array = array(
            'name' => $packageName,
            'manifest' => file_get_contents($package -> phar['manifest.json']),
            'installed_as' => 'manual', 'version' => $packageMeta->version,
            'release' => $packageMeta->release,
            'status' => 'broken',
        );
        
        $panthera -> db -> query('INSERT INTO `{$db_prefix}leopard_packages` (`id`, `name`, `manifest`, `installed_as`, `version`, `release`, `status`) VALUES (NULL, :name, :manifest, :installed_as, :version, :release, :status)', $array);

        // create backup directory
        $backupDir = SITE_DIR. '/content/packages/' .$packageName. '-' .$packageMeta->version. '-' .$packageMeta->release;
        $panthera -> logging -> output ('Creating package directory "' .$backupDir. '"', 'leopard');
        $dontBackup = False;

        if (!is_dir(SITE_DIR. '/content/packages/'))
            @mkdir(SITE_DIR. '/content/packages/');

        if (!is_dir($backupDir))
            @mkdir($backupDir);
        else
            $dontBackup = True; // dont overwrite old backup after package reinstall, etc.

        // copy package file
        $panthera -> logging -> startTimer();
        copy($packageFile, $backupDir. '/' .$packageName. '.phar');
        $panthera -> logging -> output('Copying package file to ' .$backupDir. '/' .$packageName. '.phar', 'leopard');

        // installing files
        foreach ((array)$packageMeta->files as $file => $sum)
        {
            if ($file == 'leopard.hooks.php')
                continue;

            $panthera -> logging -> output('Installing "' .$file. '" to ' .SITE_DIR. '/' .$file, 'leopard');
            $panthera -> logging -> output('path=' .$package->phar[$file]->getPathName(), 'leopard');
            $contents = file_get_contents($package->phar[$file]->getPathName());

            // first make a backup of file that will be overwritten
            if (is_file(SITE_DIR. '/' .$file) and $dontBackup == False)
            {
                $panthera -> logging -> output ('Copying original file to backup "' .SITE_DIR. '/' .$file. '" -> "' .$backupDir. '/' .md5($file). '"', 'leopard');
                copy(SITE_DIR. '/' .$file, $backupDir. '/' .md5($file));
            }

            if (!is_dir(dirname(SITE_DIR. '/' .$file)))
            {
                $exp = explode('/', dirname($file));
                $absolute = SITE_DIR. '/';
                $relative = '';

                foreach ($exp as $dir)
                {
                    $absolute .= $dir. '/';
                    $relative .= $dir .'/';

                    if (!is_dir($absolute))
                    {
                        $array = array('path' => $relative, 'sum' => '*DIRECTORY*', 'package' => $packageName, 'dependencies' => serialize(array()));
                        $panthera -> db -> query('INSERT INTO `{$db_prefix}leopard_files` (`id`, `path`, `md5`, `package`, `created`, `dependencies`) VALUES (NULL, :path, :sum, :package, NOW(), :dependencies)', $array);
                    }
                }

                if (!mkdir(dirname(SITE_DIR. '/' .$file), 0755, true)) // recursive mkdir
                {
                    $panthera -> logging -> output ('Cannot create "' .SITE_DIR. '/' .$file. '" directory', 'leopard');
                    continue;
                }
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

        // update Panthera autoloader cache
        $panthera -> importModule('autoloader.tools');
        pantheraAutoloader::updateCache();

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
        $panthera = pantheraCore::getInstance();

        // TODO: Dependency support and option to remove only single package without its dependencies

        $packageName = self::packageName($packageName);

        $panthera -> logging -> output('Preparing to remove "' .$packageName. '" package', 'leopard');

        if (!self::checkInstalled($packageName))
        {
            $panthera -> logging -> output ('Package is not installed, nothing to remove', 'leopard');
            return False; // package is not installed
        }

        $package = self::getInstalled($packageName);
        $packageMeta = (object)$package -> info;

        $backupDir = SITE_DIR. '/content/packages/' .$packageName. '-' .$packageMeta->version. '-' .$packageMeta->release;

        if (is_file($backupDir. '/' .$packageName. '.phar'))
        {
            $p = new Phar($backupDir. '/' .$packageName. '.phar');

            try {
                $p['leopard.hooks.php'];
                include $p['leopard.hooks.php'];
            } catch (Exception $e) {
                $panthera -> logging -> output ('No leopard.hooks.php file found in package archive', 'leopard');
            }
        }

        // pre-remove hooks
        $panthera -> logging -> output('Running pre-remove hooks', 'leopard');
        $package = $panthera -> get_filters('leopard.preremove', $package);
        $package = $panthera -> get_filters('leopard.preremove.' .$packageName, $package);

        foreach ((array)$package->files as $file => $sum)
        {
            if (is_dir($file))
            {
                $dirsToRemove[] = $file;
                continue;
            }


            $panthera -> logging -> output('Removing "' .$file. '" file from filesystem', 'leopard');
            unlink($file);

            if ($dontRestoreBackup == False)
            {
                $sum = md5($file);

                if (is_file($backupDir. '/' .$sum))
                    copy($backupDir. '/' .$sum, $file);
            }
        }

        $panthera -> logging -> output('Cleaning up empty directories', 'leopard');

        rsort($dirsToRemove);

        foreach ($dirsToRemove as $dir)
        {
            $panthera -> logging -> output('Removing directory "' .$dir. '"', 'leopard');
            rmdir($dir);
        }

        // post-remove hooks
        $panthera -> logging -> output('Running post-remove hooks', 'leopard');

        // update Panthera autoloader cache
        $panthera -> importModule('autoloader.tools');
        pantheraAutoloader::updateCache();

        $package = $panthera -> get_filters('leopard.postremove', $package);
        $package = $panthera -> get_filters('leopard.postremove.' .$packageName, $package);

        // clean up
        $panthera -> logging -> output ('Cleaning up backup directory', 'leopard');
        $panthera -> importModule('filesystem');
        filesystem::deleteDirectory($backupDir);

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

class leopardException extends Exception {}
