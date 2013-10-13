<?php
/**
  * Make backup of database
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

// TODO: SQLite3 support

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_manage_sql_dumps')) 
{
    $noAccess = new uiNoAccess;
    $noAccess -> display();
}

$panthera -> importModule('mysqldump');
$panthera -> importModule('filesystem');

if (isset($_GET['get']))
{
    $file = addslashes(str_replace('../', '', $_GET['get']));

    if (is_file(SITE_DIR. '/content/backups/db/' .$file))
    {
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' .$file. '"');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        print(file_get_contents(SITE_DIR. '/content/backups/db/' .$file));
        pa_exit();
    }
}

// make a SQL dump
if (isset($_POST['dump']))
{
    if ($panthera->db->getSocketType() == 'mysql')
        $name = $panthera->config->getKey('db_name'). '-' .date('Y.m.d_G:i:s'). '.sql';

    $dump = SQLDump::make();

    if ($dump != '')
    {
        $fp = fopen(SITE_DIR. '/content/backups/db/' .$name, 'wb');
        fwrite($fp, $dump);
        fclose($fp);

        ajax_exit(array('status' => 'success', 'message' => localize('Done')));
    }

    ajax_exit(array('status' => 'failed'));
}

if ($_GET['action'] == 'settings')
{
    $job = new crontab('jobname', 'sqldump');
    
    if (!$job -> exists())
    {
        crontab::createJob('sqldump', array('SQLDump', 'cronjob'), '', '*', '*', '*/7'); // 7 days interval by default
        $job = new crontab('jobname', 'sqldump');
    }
    
    if (isset($_POST['timeInterval']))
    {
        $string = crontab::getDefaultIntervals($_POST['getDefaultIntervals']);
    }
    
    $titlebar = new uiTitlebar(localize('Automatic backup settings', 'database'));
    $panthera -> template -> push('jobInterval', crontab::getIntervalExpression());
    $panthera -> template -> push('cronIntervals', crontab::getDefaultIntervals());
    $panthera -> template -> display('settings.sqldump.tpl');
    pa_exit();
}

/**
  * Show all created dumps
  *
  * @author Damian Kęska
  */

$uiPager = new uiPager('adminSQLDumps', SQLDump::getSQLDumps(False));
$uiPager -> setActive(intval($_GET['page']));
$uiPager -> setLinkTemplatesFromConfig('sqldump.tpl');
$limit = $uiPager -> getPageLimit();
$dumps = SQLDump::getSQLDumps($limit[0], $limit[1]);

$dumpsTpl = array();

foreach ($dumps as $dump)
{
    $dumpsTpl[] = array(
        'name' => basename($dump),
        'size' => bytesToSize(filesize($dump)),
        'date' => date('G:i:s d.m.Y', filemtime($dump))
    );
}

$panthera -> template -> push('dumps', $dumpsTpl);
$titlebar = new uiTitlebar(localize('Backup your database to prevent data loss', 'database'));
$panthera -> template -> display('sqldump.tpl');
pa_exit();
