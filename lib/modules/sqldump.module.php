<?php
/**
 * MySQL & SQLite3 backup
 * 
 * @package Panthera\core\system\database
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
    exit;

/**
 * MySQL & SQLite3 backup
 * 
 * @package Panthera\core\system\database
 * @author Damian Kęska
 */

class SQLDump
{
    /**
     * How many SQL data rows keep in memory before saving to file
     * 
     * @var $rowBuffer
     */
    
    public static $rowBuffer = 100;
    
    /**
     * Dump MySQL Database
     *
     * @param string $selectedTables Optional - array of single tables to dump
     * @param bool $backupData Backup all data, set to False to backup only structure
     * @param bool $resultArray Return array instead of string
     * @param bool $replacePrefix Replace tables prefix with {$db_prefix}
     * @return string Path to SQL dump file
     */

    public static function make($selectedTables='*', $backupData=True, $resultArray=False, $replacePrefix=False, $config='')
    {
        static::validatePaths();
        
        if (!$config)
            $config = panthera::getInstance() -> config -> getConfig();
        
        $newDB = new pantheraDB(panthera::getInstance(), $config);
        
        $tables = $newDB -> listTables();
        $tmp = panthera::getTmp();
        $fp = fopen($tmp, 'w');
        
        foreach ($tables as $table)
        {
            if (is_array($selectedTables) and !in_array($table, $selectedTables))
                continue;
            
            $tableWPrefix = $table;
            $str = "# ==== ".$table." table\n\n".$newDB -> showCreateTable($table)."\n\n";

            if ($replacePrefix)
            {
                $tableWPrefix = str_replace($config['db_prefix'], '{$db_prefix}', $table);
                $str = str_replace($config['db_prefix'], '{$db_prefix}', $str);
            }

            fwrite($fp, $str);
            
            if ($backupData)
            {
                $totalRows = $newDB -> getRows(str_replace($config['db_prefix'], '', $table), '', False, False, '', '');
                $readBuffer = '';
                
                for ($i=0; $i < $totalRows; $i+=static::$rowBuffer)
                {
                    panthera::getInstance() -> logging -> output('Dumping ' .$i. '/' .$totalRows. ' from ' .$table, 'sqldump');
                    $rows = $newDB -> getRows(str_replace($config['db_prefix'], '', $table), '', static::$rowBuffer, $i, '', '');
                    
                    foreach ($rows as $row)
                        $readBuffer .= $newDB -> exportRow($tableWPrefix, $row). "\n";
                    
                    // commit changes
                    fwrite($fp, $readBuffer);
                }
            }
        }
        
        fclose($fp);
        return $tmp;
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
        static::validatePaths();
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
                    $rFiles[] = SITE_DIR. '/content/backups/db/' .$file;

                // if limit was reached
                if ($i >= ($limitFrom+$count) and $count > 0)
                    break;
            }
        }

        if ($limitFrom === False)
            return $i;

        return $rFiles;
    }
    
    
    /**
     * Validate SQL dumps operational paths, create if not exists
     * 
     * @author Damian Kęska
     * @return bool
     */
     
    public static function validatePaths()
    {
        if (!is_dir(SITE_DIR. '/content/backups'))
            mkdir(SITE_DIR. '/content/backups');
        
        if (!is_dir(SITE_DIR. '/content/backups/db/'))
            mkdir(SITE_DIR. '/content/backups/db');
        
        return is_dir(SITE_DIR. '/content/backups/db');
    }

    /**
     * Backup job for crontab
     *
     * @param mixed $data
     * @return $data
     * @author Damian Kęska
     */

    public static function cronjob($data='')
    {
        static::validatePaths();
        $panthera = pantheraCore::getInstance();

        if ($panthera->db->getSocketType() == 'mysql')
            $name = $panthera->config->getKey('db_name'). '-' .date('Y.m.d_H:i:s'). '.sql';
        else
            $name = date('Y.m.d_H:i:s'). '-' .$panthera->config->getKey('db_file'). '.sql';

        $dump = SQLDump::make();

        if ($dump)
        {
            $fp = fopen(SITE_DIR. '/content/backups/db/' .$name, 'wb');
            fwrite($fp, $dump);
            fclose($fp);

            print("Wrote backup to ".SITE_DIR. "/content/backups/db/" .$name."\n");
        } else
            print("Cannot make a dump");
        

        return $data;
    }
}