<?php
/**
  * SQL dump in PHP module
  * @package Panthera\modules\database
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if(!class_exists('Backup_Database'))
    include(PANTHERA_DIR. '/share/mysqldump.php');

/**
 * Dump MySQL Database
 *
 * @param string $tables Optional - array of single tables to dump
 * @return string SQL dump
 */

function sqldump($tables='*')
{
    global $panthera;
    $backupDatabase = new Backup_Database($panthera->config->getKey('db_host'), $panthera->config->getKey('db_user'), $panthera->config->getKey('db_password'), $panthera->config->getKey('db_name'));
    return $backupDatabase->backupTables($tables);
}

/**
 * Get all SQL dumps. Supports SQL-Like limit. eg. LIMIT $limitFrom, $count
 *
 * @param int $limitFrom SQL-like limit (position)
 * @param int $count SQL-like number of elements to return
 * @return string SQL dump
 */

function getSQLDumps($limitFrom=0, $count=0)
{
    $files = scandir(SITE_DIR. '/content/backups/db/'); // all files
    $rFiles = array(); // selected files
    $i = 0;

    $files = array_reverse($files);

    foreach ($files as $file)
    {
        $pathInfo = pathinfo($file);

        if (strtolower($pathInfo['extension']) != 'sql')
            continue;

        $i++;

        // start from limit
        if ($i >= $limitFrom)
        {
            $rFiles[] = SITE_DIR. '/content/backups/db/' .$file;
        }

        // if limit was reached
        if ($i >= ($limitFrom+$count) and $count > 0)
            break;
    }
    
    return $rFiles;
}
