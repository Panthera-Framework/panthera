<?php
/**
  * Browse file
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'browsefile.tpl';

if (!getUserRightAttribute($user, 'can_see_site_files')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('files');
$panthera -> importModule('filesystem');

// create back button
if (isset($_GET['back_btn']))
    $template -> push('back_btn', base64_decode($_GET['back_btn']));


$path = str_replace('../', '', str_ireplace(PANTHERA_DIR, '', str_ireplace(SITE_DIR, '', $_GET['path'])));

if (is_file(PANTHERA_DIR. '/' .$path) or is_file(SITE_DIR. '/' .$path))
{
    if (is_file(PANTHERA_DIR. '/' .$path))
        $filePath = realpath(PANTHERA_DIR. '/' .$path);
    else
        $filePath = realpath(SITE_DIR. '/' .$path);

    $pathInfo = pathinfo($filePath);
    $allowedExtensions = array('php', 'inc', 'txt', 'js', 'tpl', 'po', 'jpg', 'png');

    // images and binary files we cant show
    if (!in_array($pathInfo['extension'], $allowedExtensions))
    {
        $template -> push('err', localize('cannot show binary files and images'));
        $template -> display($tpl);
        pa_exit();
    }

    $mime = filesystem::getFileMimeType($filePath);
    $type = filesystem::fileTypeByMime($mime);

    // we cannot show config.php
    if (str_ireplace(SITE_DIR, '', $filePath) == '/content/app.php')
    {
        $template -> push('err', localize('file not found'));
        $template -> display($tpl);
        pa_exit();
    }

    if ($type == 'image')
    {
        $fileLines = '<img src="' .$path. '">';
    } else {
        $fileContents = file_get_contents($filePath);
        $fileLines = filesystem::printCode($fileContents, intval($_GET['start']), intval($_GET['end']));
    }


    $owner = @posix_getpwuid(@fileowner($filePath));
    $group = posix_getgrgid(filegroup($filePath));

    $template -> push('file_path', $filePath);
    $template -> push('contents', $fileLines);
    $template -> push('perms', substr(sprintf('%o', fileperms($filePath)), -4));
    $template -> push('owner', $owner['name']);
    $template -> push('group', $group['name']);
    $template -> push('size_bytes', filesize($filePath));
    $template -> push('size', filesystem::bytesToSize(filesize($filePath)));
    $template -> push('modification_time', date($panthera -> dateFormat, filemtime($filePath)));
    $template -> push('mime', $mime);
    $template -> push('type', $type);
    $template -> push('action', 'view');
} else {
    $template -> push('err', localize('file not found'));
}