<?php
/**
  * Requirements check
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
$requiredExtensions = array('pcre' => 'any', 'hash' => 'any', 'fileinfo' => 'any', 'json' => 'any', 'session' => 'any', 'Reflection' => 'any', 'Phar' => 'any', 'PDO' => 'any', 'gd' => 'any', 'pdo_mysql' => 'any', 'pdo_sqlite' => 'any');
$optionalExtensions = array('mcrypt' => 'any', 'curl' => 'any', 'memcached' => 'any', 'XCache' => 'any', 'apc' => 'any', 'xdebug' => 'any', 'redis' => 'any', 'memcache' => 'any');
$requiredPHPVersion = '5.2.0';

// errors count
$errors = 0;

if ($installer->config->requiredPHPVersion)
    $requiredPHPVersion = $installer->config->requiredPHPVersion;
    
if ($installer->config->requiredExtensions)
    $requiredExtensions = array_merge($requiredExtensions, $installer->config->requiredExtensions);
    
if ($installer->config->optionalExtensions)
    $optionalExtensions = array_merge($optionalExtensions, $installer->config->optionalExtensions);

$requirements = array();

// php version requirement
$requirements['PHP'] = array('installed' => phpversion(), 'required' => $requiredPHPVersion, 'passed' => True);

// check PHP version
if (strnatcmp(phpversion(), $requiredPHPVersion) < 0)
{
    $errors++;
    $requirements['PHP']['note'] = localize('Your PHP is outdated, please upgrade', 'installer');
    $requirements['PHP']['passed'] = False;
}

// all required extensions
foreach ($requiredExtensions as $extension => $version)
{
    $requirements[$extension] = array('installed' => localize('Yes', 'installer'), 'required' => slocalize('any for >=%s PHP version', 'installer', $requiredPHPVersion), 'passed' => True);

    if (!extension_loaded($extension))
    {
        $errors++;
        $requirements[$extension]['passed'] = False;
        $requirements[$extension]['installed'] = localize('No', 'installer');
        continue;
    }
    
    if ($version != 'any')
    {
        if (strnatcmp(phpversion($extension), $version) < 0)
        {
            $errors++;
            $requirements[$extension]['passed'] = False;
            $requirements[$extension]['installed'] = phpversion($extension);
            $requirements[$extension]['required'] = $version;
        }
    }
    
    if (phpversion($extension))
    {
        $requirements[$extension]['installed'] = phpversion($extension);
    }
}

foreach ($optionalExtensions as $extension => $version)
{
    $requirements[$extension] = array('installed' => localize('Yes', 'installer'), 'required' => slocalize('any for >=%s PHP version', 'installer', $requiredPHPVersion), 'passed' => True);
    
    if (!extension_loaded($extension))
    {
        $requirements[$extension]['passed'] = 'optional';
        $requirements[$extension]['installed'] = localize('No', 'installer');
        continue;
    }
    
    if ($version != 'any')
    {
        if (strnatcmp(phpversion($extension), $version) < 0)
        {
            $errors++;
            $requirements[$extension]['passed'] = False;
            $requirements[$extension]['installed'] = phpversion($extension);
            $requirements[$extension]['required'] = $version;
        }
    }
    
    if (phpversion($extension))
    {
        $requirements[$extension]['installed'] = phpversion($extension);
    }
}


// requirements met, ready for next step
if ($errors == 0)
{
    $installer -> enableNextStep();
}

$panthera -> template -> push('requirements', $requirements);
$installer -> template = 'environment';
