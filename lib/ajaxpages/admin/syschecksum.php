<?php
/**
  * Check MD5 sums
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'syschecksum.tpl';

if (!getUserRightAttribute($user, 'can_manage_debug')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');

$panthera -> importModule('filesystem');

$files = scandirDeeply(SITE_DIR);
$filesTpl = array();
$array = '';

if(isset($_FILES['syschecksum']))
{
    if(is_file($_FILES['syschecksum']['tmp_name']))
    {
        $array = @unserialize(file_get_contents($_FILES['syschecksum']['tmp_name']));
        @unlink($_FILES['syschecksum']['tmp_name']);
    }
}

foreach ($files as $file)
{
    if(!is_file($file))
        continue;

    $contents = file_get_contents($file);
    $nameSum = md5(str_replace(SITE_DIR, '', $file));
    $sum = md5($contents);
    $bold = False;

    if (is_array($array))
    {
        // check if remote server has the file
        if (isset($array["panthera_checksum"][$nameSum]))
        {
            // the file has diffirences
            if ($_POST['method'] == 'sum')
            {
                // checksum method
                if($array["panthera_checksum"][$nameSum]['sum'] != $sum)
                    $bold = True;
            } elseif ($_POST['method'] == 'time') {
                // by modification time
                if($array["panthera_checksum"][$nameSum]['mtime'] != filemtime($file))
                    $bold = True;
            } else {
                // checking by file size
                if($array["panthera_checksum"][$nameSum]['size_bytes'] != strlen($contents))
                    $bold = True;
            }

            if (@$_POST['show_only_modified'] == "1")
            {
                if ($bold == False)
                   continue;
            }
        }
    }

    $filesTpl[$nameSum] = array('name' => str_replace(SITE_DIR, '', $file), 'sum' => $sum, 'size' => bytesToSize(strlen($contents)), 'time' => date('G:i:s d.m.Y', filemtime($file)), 'mtime' => filemtime($file), 'bold' => $bold, 'size_bytes' => strlen($contents));
}

// add new files to list that does not exists on one of servers
if (is_array($array))
{
    foreach ($array['panthera_checksum'] as $file)
    {
        $filesTpl[$nameSum] = array('name' => $file['name'], 'sum' => $file['sum'], 'size' => $file['size'], 'time' => $file['time'], 'mtime' => $file['mtime'], 'bold' => True, 'size_bytes' => $file['size_bytes'], 'created' => True);
    }
}

if (isset($_GET['export']))
{
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="syschecksum.bin"');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    print(serialize(array('panthera_checksum' => $filesTpl)));
    pa_exit();
}

$template -> push('files', $filesTpl);

$titlebar = new uiTitlebar(localize('Checksum of system files', 'debug'));

?>
