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

$tpl = 'sqldump.tpl';

if (!getUserRightAttribute($user, 'can_manage_sql_dumps')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('database');

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
        $name = $panthera->config->getKey('db_name'). '-' .date('G:i:s_d.m.Y'). '.sql';

    $dump = sqldump();

    if ($dump != '')
    {
        $fp = fopen(SITE_DIR. '/content/backups/db/' .$name, 'wb');
        fwrite($fp, $dump);
        fclose($fp);

        ajax_exit(array('status' => 'success'));
    }

    ajax_exit(array('status' => 'failed'));
}

//var_dump(mysqldump());

$dumps = getSQLDumps();
$dumpsTpl = array();

foreach ($dumps as $dump)
{
    $dumpsTpl[] = array('name' => basename($dump), 'size' => bytesToSize(filesize($dump)), 'date' => date('G:i:s d.m.Y', filemtime($dump)));
}

$template -> push('dumps', $dumpsTpl);
?>
