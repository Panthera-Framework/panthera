<?php
/**
  * Manage locales
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'locales.tpl';

if (!getUserRightAttribute($user, 'can_update_locales')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('locales');

$locales = $panthera->locale->getLocales();
$systemDefault = $panthera->locale->getSystemDefault();

switch ($_GET['action'])
{
    case 'set_as_default':
        $panthera -> locale -> setSystemDefault($_POST['id']);
        $systemDefault = $panthera->locale->getSystemDefault();
    break;

    case 'delete':
        $panthera -> locale -> removeLocale($_POST['id']);
    break;

    case 'add':
        $panthera -> locale -> addLocale($_POST['id']);
    break;

    case 'toggle_visibility':
        $visibility = $locales[$_POST['id']];
        $panthera -> locale -> toggleLocale($_POST['id'], !$visibility);
    break;
}

$template -> push('locale_system_default', $systemDefault);

$tmp = scandir(SITE_DIR. '/content/locales/');
$avaliableLocales = array();
$avaliableLocales[] = 'english';

foreach ($tmp as $value)
{
    if (array_key_exists($value, $locales))
        continue;

    if(is_dir(SITE_DIR. '/content/locales/' .$value. '/C/LC_MESSAGES'))
        $avaliableLocales[] = $value;
}

$locales = array();

foreach ($panthera->locale->getLocales() as $locale => $visibility)
{
    $locales[$locale]['visibility'] = $visibility;

    if (is_file(SITE_DIR. '/images/admin/flags/' .$locale. '.png'))
        $locales[$locale]['flag'] = TRUE;
    else
        $locales[$locale]['flag'] = FALSE;
}

$template -> push('locales_dir', $avaliableLocales);
$template -> push('locales_added', $locales);
$template -> push('loaded_domains', $panthera->locale->getLoadedDomains());
$template -> push('action', $_GET['action']);

?>
