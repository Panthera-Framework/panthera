<?php
/**
 * Database configuration for pantheraInstaller
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('PANTHERA_INSTALLER'))
    return False;

/**
 * Database configuration for pantheraInstaller
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class databaseInstallerControllerSystem extends installerController
{
    /**
     * Main function executed right after controller will be called
     *
     * @return null
     */

    public function display()
    {
        // reset table collision decision after page refresh
        if ($this -> panthera -> session -> exists('installer.database'))
        {
            $t = $this -> panthera -> session -> get('installer.database');
            unset($t['collisionsSelection']);
            $this -> panthera -> session -> set('installer.database', $t);
        }

        // dispatch actions manually
        if (isset($_POST['db_prefix']))
            $this -> dispatchAction('checkDatabaseConnection');
        elseif (isset($_POST['createDBFile']))
            $this -> dispatchAction('createDBFile');
        elseif (isset($_POST['save']))
            $this -> dispatchAction('saveConfiguration');

        $databaseSettings = array();

        // settings already loaded
        if ($this -> panthera -> config -> getKey('db_prefix'))
            $databaseSettings['db_prefix'] = $this -> panthera -> config -> getKey('db_prefix');

        if ($this -> panthera -> config -> getKey('db_socket'))
            $databaseSettings['db_socket'] = $this -> panthera -> config -> getKey('db_socket');

        if ($this -> panthera -> config -> getKey('db_password'))
            $databaseSettings['db_password'] = $this -> panthera -> config -> getKey('db_password');

        if ($this -> panthera -> config -> getKey('db_file'))
            $databaseSettings['db_file'] = str_replace('.sqlite3', '', $this -> panthera -> config -> getKey('db_file'));

        if ($this -> panthera -> config -> getKey('db_host'))
            $databaseSettings['db_host'] = $this -> panthera -> config -> getKey('db_host');

        if ($this -> panthera -> config -> getKey('db_name'))
            $databaseSettings['db_name'] = $this -> panthera -> config -> getKey('db_name');

        if ($this -> panthera -> config -> getKey('db_username'))
            $databaseSettings['db_username'] = $this -> panthera -> config -> getKey('db_username');

        if ($this -> panthera -> config -> getKey('db_socket'))
            $databaseSettings['db_socket'] = $this -> panthera -> config -> getKey('db_socket');

        $this -> panthera -> template -> push('databaseSockets', 'aaa');

        $this -> panthera -> template -> push (array(
            'databaseSockets' => array(
                'sqlite' => 'file',
                'mysql' => 'server'
            ),

            'databaseSettings' => $databaseSettings,
        ));

        $this -> installer -> template = 'database';
    }

    /**
     * Check database connection
     *
     * @return null
     * @author Damian Kęska
     */

    public function checkDatabaseConnectionAction()
    {
        $config = array_merge($_POST, array(
            'cache_db' => False,
        ));

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
            $this -> panthera -> logging -> output ('Selected ' .$_POST['collisionsSelection']. ' method to solve tables collisions', 'installer');

        // make a backup
        if ($_POST['collisionsSelection'] == 'backupAndDrop')
        {
            $this -> panthera -> logging -> output ('Creating database backup for ' .$config['db_socket']. ' type of database', 'installer');

            // simply copy a database file
            if ($config['db_socket'] == 'sqlite')
            {
                copy(getContentDir('database/' .$config['db_file']), $dumpFile);
            } else {
                // with MySQL its more complicated, so create a dump and save it to file
                $this -> panthera -> importModule('mysqldump');
                $dump = new Backup_Database($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
                file_put_contents($dumpFile, $dump->backupTables('*', True));
            }
        }


        try {
            $db = new pantheraDB($this -> panthera, $config, True);
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

            ajax_exit(array(
                'status' => 'failed',
                'message' => $e->getMessage(),
                'field' => $field,
            ));
        }


        $this -> panthera -> session -> set('installer.database', $config);

	    if (!$tables)
		    $this -> installer -> enableNextStep();

        $this -> installer -> db -> save();

        ajax_exit(array(
            'status' => 'success',
            'tables' => $tables,
            'dump' => $dumpFile,
        ));
    }

    /**
     * Create a SQLite3 database file
     *
     * @return null
     * @author Damian Kęska
     */

    public function createDBFileAction()
    {
        $dbName = str_replace(array(
            '..',
            '/',
        ), '', $_POST['createDBFile']);

        $PDO = new PDO('sqlite:' .SITE_DIR. '/content/database/' .$dbName. '.sqlite3');

        if (is_file(SITE_DIR. '/content/database/' .$dbName. '.sqlite3'))
        {
            ajax_exit(array(
                'status' => 'success',
            ));
        }

        ajax_exit(array(
            'status' => 'failed',
        ));
    }

    /**
     * Import tables, save configuration
     *
     * @return null
     * @author Damian Kęska
     */

    public function saveConfigurationAction()
    {
        if ($this -> panthera -> session -> exists('installer.database'))
        {
            $this -> panthera -> importModule('appconfig');
            $app = new appConfigEditor();

            // a little bit clean up
            $config = $this -> panthera -> session -> get('installer.database');
            unset($config['disable_overlay']);
            unset($config['collisionsSelection']);

            $app -> config = (object)array_merge((array)$app -> config, $config);

            $db = new pantheraDB($this -> panthera, (array)$app -> config, True);
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
                    $this -> panthera -> logging -> output ('Skipping table "' .$file. '" import, table exists', 'installer');
                    continue;
                }

                $this -> panthera -> logging -> output ('Creating table ' .$db -> prefix. str_ireplace('.sql', '', $file), 'installer');

                try {
                    $db -> execute(file_get_contents($dumpFile));
                } catch (Exception $e) {
                    $errors[$db -> prefix. str_ireplace('.sql', '', $file)] = $e -> getMessage();
                }
            }

            if (count($errors) > 0)
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'errors' => $errors,
                ));
            }

            // set database variables
            $this -> panthera -> db = $db;
            $this -> preconfigureDatabase();

            $this -> installer -> enableNextStep();
            $app -> save();

            ajax_exit(array(
                'status' => 'success',
            ));
        }
    }

    /**
     * Preconfigure basic configuration variables
     *
     * @return null
     */

    public function preconfigureDatabase()
    {
        $this -> panthera -> config -> loadOverlay('*');
        $this -> panthera -> config -> getKey('ajax_url', $_SERVER['HTTP_HOST'].str_ireplace('install.php', '_ajax.php', $_SERVER['SCRIPT_NAME']), 'string');
        $this -> panthera -> config -> getKey('site_title', array('english' => 'Panthera Framework'), 'array');
        $this -> panthera -> config -> getKey('site_description', array('english' => 'Another site based on Panthera Framework'), 'array');
        $this -> panthera -> config -> getKey('site_metas', array('english' => 'another, panthera, framework, based, site'), 'array');
        $this -> panthera -> config -> getKey('locale.default', 'english', 'string');
        $this -> panthera -> config -> getKey('template', 'example', 'string');
        $this -> panthera -> config -> getKey('debug', true, 'bool');
        $this -> panthera -> config -> getKey('salt', md5(generateRandomString(8096)), 'string');
        $this -> panthera -> config -> getKey('template_debugging', true, 'bool');
        $this -> panthera -> config -> getKey('template_caching', true, 'bool');
        $this -> panthera -> config -> getKey('template_cache_lifetime', 120, 'int');
        $this -> panthera -> config -> getKey('redirect_after_login', 'index.php');
        $this -> panthera -> config -> getKey('languages', array('polski' => True, 'english' => True));
        $this -> panthera -> config -> getKey('session_lifetime', 86400, 'int');
        $this -> panthera -> config -> save();
    }
}