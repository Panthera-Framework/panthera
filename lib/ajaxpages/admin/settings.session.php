<?php
/**
  * Session configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_session_settings'))
{
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> importModule('admin/ui.settings');
$panthera -> config -> getKey('cookie_encrypt', 1, 'bool');
$panthera -> locale -> loadDomain('session');
$panthera -> locale -> loadDomain('installer');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings;
$config -> add('session_useragent', localize('Strict browser check', 'session'), new integerRange(0, 1));
$config -> add('session_lifetime', localize('Session life time', 'installer'), new integerRange(0, 999999));
$config -> add('cookie_encrypt', localize('Encrypt cookies', 'installer'), new integerRange(0, 1));
$config -> add('gzip_compression', localize('GZip compression', 'session'), new integerRange(0, 1));
$config -> add('header_maskphp', localize('Mask PHP version', 'installer'), new integerRange(0, 1));
$config -> add('header_framing', localize('X-Frame', 'installer'), array(
    'sameorigin' => localize('Only on same domain', 'installer'), 
    'allowall' => localize('Yes', 'installer'),
    'deny' => localize('No', 'installer')
));

$config -> add('header_xssprot', localize('IE XSS-Protection', 'installer'), new integerRange(0, 1));
$config -> add('header_nosniff', localize('No-sniff header', 'installer'), new integerRange(0, 1));
$config -> add('hashing_algorithm', localize('Password hashing method', 'installer'), array(
    'blowfish' => 'blowfish - ' .localize('Slower, but provides maximum security', 'installer'),
    'md5' => 'md5 - ' .localize('Faster, but very weak', 'installer'), 
    'sha512' => 'sha512 - ' .localize('Fast, and provides medium security level', 'installer')
));

$config -> setDescription('header_xssprot', localize('Tell\'s Internet Explorer to turn on XSS-Protection mechanism', 'installer'));
$config -> setDescription('session_useragent', localize('Useragent strict check', 'installer'));
$config -> setDescription('cookie_encrypt_key', localize('Cookies can be encrypted with strong algorithm, so the user wont be able to read contents', 'installer'));
$config -> setDescription('session_lifetime', localize('Maximum time user can be idle (in seconds)', 'installer'));
$config -> setDescription('header_framing', localize('Allow your website to be framed using iframe tag', 'installer'));
$config -> setDescription('header_maskphp', localize('Force HTTP server to show false informations about PHP version', 'installer'));
$config -> setDescription('hashing_algorithm', localize('Strong hashing algorithms are great in cases when site\'s database leaks in to the web, the hackers would have a problem with reading a strongly hashed and salted password', 'installer'));
$config -> setDescription('header_nosniff', localize('This can reduce some drive-by-download attacks', 'installer'));
$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> push ('title', localize('Session, cookies and browser security settings', 'session'));
$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
