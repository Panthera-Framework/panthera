<?php
/**
  * Installer front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
session_start();
error_reporting(E_ERROR);

// load app.php and extract $config variable
$app = @file_get_contents('content/app.php');
$configExported = substr($app, strpos($app, '$config'), strpos($app, ');')-4);
@eval($configExported);
$newAppFile = False;

if (!is_array($config))
{
    $newAppFile = True;
    $config = array();
}

if (isset($config['installed']) and $config['installed'])
{
    header('Location: pa-login.php');
    exit;
}

if ($config['preconfigured'] !== True)
{
    // pre-configure installer environment
    $config['build_missing_tables'] = True;
    
    if (!isset($config['db_socket']))
    {
        $config['db_socket'] = 'sqlite';
        $config['db_file'] = 'db.sqlite3';
    }
    
    $config['SITE_DIR'] = dirname($_SERVER['SCRIPT_FILENAME']);
    $config['disable_overlay'] = True;
    $config['debug'] = True;
    $config['debug_to_varcache'] = True;
    $config['debug_to_file'] = True;
    
    // remove "/" at the end of string
    if (substr($config['SITE_DIR'], -1) == '/')
    {
        $config['SITE_DIR'] = substr($config['SITE_DIR'], 0, -1);
    }
    
    if (!is_file($config['SITE_DIR']. '/content/database/' .$config['db_file']))
    {
        file_put_contents($config['SITE_DIR']. '/content/database/' .$config['db_file'], '');
    }
    
    if (!isset($config['url']))
    {
        $protocol = 'http';

        if ($_SERVER['HTTPS'])
            $protocol = 'https';

        $config['url'] = $protocol. '://' .str_replace('//', '/', $_SERVER['HTTP_HOST'].str_replace(basename($_SERVER['REQUEST_URI']), '', $_SERVER['REQUEST_URI']));
    }
    
    if (!isset($config['upload_dir']))
        $config['upload_dir'] = 'content/uploads';
        
    if (!isset($config['db_prefix']))
        $config['db_prefix'] = 'pa_';
        
    $config['requires_instalation'] = True;
    
    if (!isset($config['timezone']))
        $config['timezone'] = 'UTC';

    // if lib directory is not provided try to get it manually
    if (!is_dir($config['lib']))
    {
        // if installer front controller is a symlink we can find Panthera library directory in very easy way
        if (is_link($_SERVER['SCRIPT_FILENAME']))
        {
            $config['lib'] = dirname(str_ireplace('/frontpages', '', readlink($_SERVER['SCRIPT_FILENAME']))). '/';
        }
        
        // search in parent directory
        if (is_file('../lib/panthera.php'))
        {
            $config['lib'] = realpath('../lib'). '/';
        }
    }

    $config['preconfigured'] = True;

    // save changes to file
    if ($newAppFile == True)
        $app = "<?php\n\$config = ".var_export($config, True).";\n\nrequire \$config['lib']. '/boot.php';"; // creating new configuration
    else
        $app = str_replace($configExported, '$config = ' .var_export($config, True). ';', $app); // updating existing

    $fp = @fopen('content/app.php', 'w');
    
    if (!$fp)
    {
        die('Cannot write to content/app.php, please check permissions');
    }
    
    fwrite($fp, $app);
    fclose($fp);
}

define('PANTHERA_FORCE_DEBUGGING', True);
define('SKIP_CACHE', False);

// app starts here
require_once $config['lib']. '/boot.php';

// initialize installer
$panthera -> locale -> loadDomain('installer');
$panthera -> importModule('pantherainstaller');
$installer = new pantheraInstaller($panthera);

// template options
$panthera -> template -> setTemplate('installer');
$panthera -> template -> setTitle('Panthera Installer');

$installer -> loadStep();
$installer -> display();
$installer -> db -> save();
