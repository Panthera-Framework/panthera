<?php
/**
  * SQL dump in PHP module
  * @package Panthera\modules\database
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
if(!class_exists('Backup_Database'))
    include(PANTHERA_DIR. '/share/mysqldump.php');

class SQLDump
{
    /**
     * Dump MySQL Database
     *
     * @param string $tables Optional - array of single tables to dump
     * @param bool $backupData Backup all data, set to False to backup only structure
     * @param bool $resultArray Return array instead of string
     * @param bool $replacePrefix Replace tables prefix with {$db_prefix}
     * @return string SQL dump
     */

    public static function make($tables='*', $backupData=True, $resultArray=False, $replacePrefix=False)
    {
        $backupDatabase = self::initSQLDump();
        
        if ($resultArray == True)
            $backupDatabase -> resultType = "array";
            
        if ($replacePrefix == True)
            $backupDatabase -> replacePrefix = True;
        
        return $backupDatabase->backupTables($tables, $backupData);
    }

    /**
      * Create a new, configured instance of Backup_Database class
      *
      * @return object 
      * @author Damian Kęska
      */

    public static function initSQLDump()
    {
        global $panthera;
        return new Backup_Database($panthera->config->getKey('db_host'), $panthera->config->getKey('db_user'), $panthera->config->getKey('db_password'), $panthera->config->getKey('db_name'));
    }

    public static function createTemplatesFromDB($dropTables=True)
    {
        // TODO: SQLite3 support
        $backup = self::initSQLDump();
        $backup -> resultType = "array";
        $backup -> replacePrefix = True;
        $backup -> dropTables = $dropTables;
        
        $structure = $backup -> backupTables('*', False);
        
        if (!is_dir(SITE_DIR. '/content/backups/db_structure'))
            mkdir(SITE_DIR. '/content/backups/db_structure');
            
        $backupDir = SITE_DIR. '/content/backups/db_structure/' .date('G:i:s_d.m.Y');
            
        // remove old directory if exists
        if (is_dir($backupDir))
        {
            $panthera -> importModule('filesystem');
            deleteDirectory($backupDir);
        }
        
        // here we will place all files
        mkdir($backupDir);
        
        foreach ($structure as $table => $SQL)
        {
            $fp = fopen($backupDir. '/' .$table. '.sql', 'w');
            fwrite($fp, $SQL);
            fclose($fp);
        }
        
        return $backupDir;
    }

    /**
     * Get all SQL dumps. Supports SQL-Like limit. eg. LIMIT $limitFrom, $count
     *
     * @param int $limitFrom SQL-like limit (position)
     * @param int $count SQL-like number of elements to return
     * @return string SQL dump
     */

    public static function getSQLDumps($limitFrom=0, $count=0)
    {
        $files = scandir(SITE_DIR. '/content/backups/db/'); // all files
        $rFiles = array(); // selected files
        $i = 0;
        $count = intval($count);        
        
        sort($files);
        $files = array_reverse($files);

        foreach ($files as $file)
        {
            $pathInfo = pathinfo($file);

            if (strtolower($pathInfo['extension']) != 'sql' and strtolower($pathInfo['extension']) != 'sqlite3')
                continue;

            $i++;

            // start from limit
            if ($limitFrom !== False)
            {
                if ($i >= $limitFrom)
                {
                    $rFiles[] = SITE_DIR. '/content/backups/db/' .$file;
                }
                
                // if limit was reached
                if ($i >= ($limitFrom+$count) and $count > 0)
                    break;
            }
        }
        
        if ($limitFrom === False)
        {
            return $i;
        }
        
        return $rFiles;
    }
    
    public static function cronjob($data='')
    {
        return $data;
    }
}
