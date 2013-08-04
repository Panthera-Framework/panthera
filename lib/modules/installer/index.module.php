<?php
/**
  * Index step in Panthera installer
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

// we will check here the PHP version and required basic modules
/*$requiredExtensions = array('pcre', 'hash', 'fileinfo', 'json', 'session', 'Reflection', 'Phar', 'PDO', 'gd', 'pdo_mysql', 'pdo_sqlite');
$optionalExtensions = array('mcrypt', 'curl', 'memcached', 'XCache', 'apc', 'xdebug');
$requiredPHPVersion = '5.2.0';

if (strnatcmp(phpversion(),'5.2.0') < 0)
{
    print('PHP version too old');
}*/

$panthera -> importModule('liblangtool');

$locales = array();
$locales['english'] = True; // english is by default

foreach (localesManagement::getLocales() as $locale => $path)
{
    if (localesManagement::getDomainDir($locale, 'installer'))
        $locales[$locale] = is_file(SITE_DIR. '/images/admin/flags/' .$locale. '.png');
}

/** List of locales, current language **/

$currentLocale = 'english';

if (localesManagement::getDomainDir($panthera -> locale -> getActive(), 'installer'))
    $currentLocale = $panthera -> locale -> getActive();
    
if (is_file(SITE_DIR. '/images/admin/flags/' .$currentLocale. '.png'))
    $panthera -> template -> push ('currentLocaleFlag', True);
    
$panthera -> template -> push ('currentLocale', $currentLocale);
$panthera -> template -> push ('languages', $locales);

/** Timezones **/

$timezones = array();
foreach (DateTimeZone::listIdentifiers() as $timezone)
{
    $time = new DateTime('NOW');
    $time -> setTimezone(new DateTimeZone($timezone));
    
    $timezones[$timezone] = $time -> format('G:i:s d.m.Y');
}

$panthera -> template -> push ('timezone', $panthera->config->getKey('timezone'));

if (isset($_GET['_timezone']))
{
    if (in_array($_GET['_timezone'], DateTimeZone::listIdentifiers()))
    {
        $panthera -> importModule('appconfig');

        try {
            $appConfig = new appConfigEditor();
            $appConfig -> config ['timezone'] = $_GET['_timezone'];
            $appConfig -> save();
            $panthera -> config -> updateConfigCache($appConfig->config);
            $panthera -> template -> push ('timezone', $panthera->config->getKey('timezone'));
        } catch (Exception $e) {
            $panthera -> template -> push ('popupError', localize('Cannot save app.php', 'installer'). ', ' .localize('exception', 'installer'). ': ' .$e->getMessage());
        }
    }
}

$panthera -> template -> push ('timezones', $timezones);
$installer -> template = 'index';
