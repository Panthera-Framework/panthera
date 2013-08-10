<?php
/**
  * Database configuration
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('PANTHERA_INSTALLER'))
    return False;
    
// we will use this ofcourse
global $panthera;
global $installer;

$panthera -> importModule('liblangtool');
$allLocales = localesManagement::getLocales();
$locales = array();


if (isset($_GET['setDefaultLanguage']))
{
    if (isset($allLocales[$_GET['setDefaultLanguage']]) or $_GET['setDefaultLanguage'] == 'english')
    {
        $panthera -> locale -> setSystemDefault($_GET['setDefaultLanguage']);
    }
    
} elseif (isset($_GET['switchLanguage'])) {

    if (isset($allLocales[$_GET['switchLanguage']]))
    {
        $installedLocales = $panthera -> locale -> getLocales();
    
        if (isset($installedLocales[$_GET['switchLanguage']]))
        {
            $panthera -> locale -> toggleLocale($_GET['switchLanguage'], !$installedLocales[$_GET['switchLanguage']]);
        } else {
            $panthera -> locale -> addLocale($_GET['switchLanguage']);
        }
    }
}

$installedLocales = $panthera -> locale -> getLocales();

foreach ($allLocales as $locale => $path)
{
    $locales[$locale] = array('default' => False, 'enabled' => False, 'icon' => False);

    // check if there is a flag for this locale
    if (is_file(SITE_DIR. '/images/admin/flags/' .$locale. '.png'))
    {
        $locales[$locale]['icon'] = True;
    }
    
    // check if this language is enabled
    if ($installedLocales[$locale] == True)
    {
        $locales[$locale]['enabled'] = True;
    }
    
    // check if this language is currently set as default
    if ($panthera->locale->getSystemDefault() == $locale)
    {
        $locales[$locale]['default'] = True;
    }
}

$installer -> enableNextStep();
$panthera -> template -> push ('defaultLocale', $panthera->locale->getSystemDefault());
$panthera -> template -> push ('locales', $locales);
$installer -> template = 'locales';
