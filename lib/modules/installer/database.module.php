<?php
/**
  * Database configuration
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('PANTHERA_INSTALLER'))
    return False;
    
// we will use this ofcourse
global $panthera;
global $installer;

/**
  * Check database connection
  *
  * @author Damian Kęska
  */

if (isset($_POST['db_prefix']))
{
    $config = array_merge($_POST, array('cache_db' => False));
    $config['db_file'] .= '.sqlite3';
    $field = '';
    $dumpDir = '';
    $dumpFile = '';

    if ($config['db_socket'] == 'sqlite')
    {
        $dumpFile = SITE_DIR. '/content/backups/db/' .$config['db_file']. '-' .date('G:i:s_d.m.Y'). '.sqlite3';
        $dumpDir = 'sqlite3';
    } else
        $dumpFile = SITE_DIR. '/content/backups/db/' .$config['db_name']. '-' .date('G:i:s_d.m.Y'). '.sql';
        
    if (isset($_POST['collisionsSelection']))
        $panthera -> logging -> output ('Selected ' .$_POST['collisionsSelection']. ' method to solve tables collisions', 'installer');
    
    // make a backup
    if ($_POST['collisionsSelection'] == 'backupAndDrop')
    {
        $panthera -> logging -> output ('Creating database backup for ' .$config['db_socket']. ' type of database', 'installer');
    
        // simply copy a database file
        if ($config['db_socket'] == 'sqlite')
        {
            copy(getContentDir('database/' .$config['db_file']), $dumpFile);
        } else {
            // with MySQL its more complicated, so create a dump and save it to file
            $panthera -> importModule('mysqldump');
            $dump = new Backup_Database($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
            file_put_contents($dumpFile, $dump->backupTables('*', True));
        }
    }
    
    
    try {
        $db = new pantheraDB($panthera, $config, True);
        $tables = array();
        $dumps = scandir(getContentDir('database/templates/' .$dumpDir));
        
        foreach ($db -> listTables() as $table)
        {
            // drop the tables
            if ($_POST['collisionsSelection'] == 'backupAndDrop' or $_POST['collisionsSelection'] == 'simplyDrop')
            {
                if (in_array(str_replace($config['db_prefix'], '', $table). '.sql', $dumps))
                {
                    $db -> query ('DROP TABLE `' .$table. '`');
                    continue;
                }
            }
            
            if ($_POST['collisionsSelection'] == 'leaveExisting')
            {
                $tables[$table] = false;
                continue;
            }
            
            $tables[$table] = in_array(str_replace($config['db_prefix'], '', $table). '.sql', $dumps);
        }
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Database fils is missing') !== False)
        {
            $field = 'db_file';
        }
    
        ajax_exit(array('status' => 'failed', 'message' => $e->getMessage(), 'field' => $field));
    }

    
    $panthera -> session -> set('installer.database', $config);
    $installer -> db -> save();
    ajax_exit(array('status' => 'success', 'tables' => $tables, 'dump' => $dumpFile));
    
/**
  * Create a new SQLite3 database
  *
  * @author Damian Kęska
  */
    
} elseif (isset($_POST['createDBFile'])) {

    $dbName = str_replace('..', '', str_replace('/', '', $_POST['createDBFile']));
    
    $PDO = new PDO('sqlite:' .SITE_DIR. '/content/database/' .$dbName. '.sqlite3');
    
    if (is_file(SITE_DIR. '/content/database/' .$dbName. '.sqlite3'))
    {
        ajax_exit(array('status' => 'success'));
    }
    
    ajax_exit(array('status' => 'failed'));
    
    
/**
  * Save configuration and import tables
  *
  * @author Damian Kęska
  */

} elseif (isset($_POST['save'])) {
    if ($panthera->session->exists('installer.database'))
    {
        $panthera -> importModule('appconfig');
        $app = new appConfigEditor();
        
        // a little bit clean up
        $config = $panthera -> session -> get('installer.database');
        unset($config['disable_overlay']);
        unset($config['collisionsSelection']);
        
        $app -> config = (object)array_merge((array)$app -> config, $config);
        
        $db = new pantheraDB($panthera, (array)$app -> config, True);
        $config = (array)$app->config;
        
        $dumpDir = '';
        $errors = array();
        
        if ($config['db_socket'] == 'sqlite')
        {
            $dumpDir = 'sqlite3';
        }
        
        $dumps = scandir(getContentDir('database/templates/' .$dumpDir));
        $tables = $db -> listTables();
        
        foreach ($dumps as $file)
        {
            $dumpFile = getContentDir('database/templates/' .$dumpDir. '/' .$file);
        
            if ($file == '..' or $file == '.' or !$dumpFile or is_dir($dumpFile))
                continue;
                
            if (in_array($db -> prefix. str_ireplace('.sql', '', $file), $tables))
            {
                $panthera -> logging -> output ('Skipping table "' .$file. '" import, table exists', 'installer');
                continue;
            }

            $panthera -> logging -> output ('Creating table ' .$db -> prefix. str_ireplace('.sql', '', $file), 'installer');
            
            try {
                $db -> execute(file_get_contents($dumpFile));
            } catch (Exception $e) {
                $errors[$db -> prefix. str_ireplace('.sql', '', $file)] = $e -> getMessage();
            }
        }
        
        if (count($errors) > 0)
        {
            ajax_exit(array('status' => 'failed', 'errors' => $errors));
        }
        
        $installer -> enableNextStep();
        $app -> save();
        ajax_exit(array('status' => 'success'));
    }
}

// reset table collision decision after page refresh
if ($panthera -> session -> exists('installer.database'))
{
    $t = $panthera -> session -> get('installer.database');
    unset($t['collisionsSelection']);
    $panthera -> session -> set('installer.database', $t);
}

$databaseSettings = array();

// settings already loaded
if ($panthera->config->getKey('db_prefix'))
    $databaseSettings['db_prefix'] = $panthera->config->getKey('db_prefix');
    
if ($panthera->config->getKey('db_socket'))
    $databaseSettings['db_socket'] = $panthera->config->getKey('db_socket');
    
if ($panthera->config->getKey('db_password'))
    $databaseSettings['db_password'] = $panthera->config->getKey('db_password');
    
if ($panthera->config->getKey('db_file'))
    $databaseSettings['db_file'] = str_replace('.sqlite3', '', $panthera->config->getKey('db_file'));
    
if ($panthera->config->getKey('db_host'))
    $databaseSettings['db_host'] = $panthera->config->getKey('db_host');
    
if ($panthera->config->getKey('db_name'))
    $databaseSettings['db_name'] = $panthera->config->getKey('db_name');
    
if ($panthera->config->getKey('db_username'))
    $databaseSettings['db_username'] = $panthera->config->getKey('db_username');
    
if ($panthera->config->getKey('db_socket'))
    $databaseSettings['db_socket'] = $panthera->config->getKey('db_socket');
    
$panthera -> template -> push ('databaseSockets', array('sqlite' => 'file', 'mysql' => 'server'));
$panthera -> template -> push ('databaseSettings', $databaseSettings);
$installer -> template = 'database';
