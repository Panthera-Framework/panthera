<?php
/**
 * Requirements check
 * 
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */
 
installerController::$searchFrontControllerName = 'environmentInstallerControllerSystem';
 
/**
 * Requirements check
 * 
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class environmentInstallerControllerSystem extends installerController
{
    /**
     * Main function that will execute first
     * 
     * @feature installer.environment.phpversion &string Required PHP version
     * @feature installer.environment.req-exts &array Required extensions
     * @feature installer.environment.opt-exts &array Optional extensions
     * @feature installer.environment.requirements &array Requirements
     * @return null
     */
    
    public function display()
    {
        // we will check here the PHP version and required basic modules
        $requiredExtensions = array(
            'pcre' => 'any',
            'hash' => 'any',
            'fileinfo' => 'any',
            'json' => 'any',
            'session' => 'any',
            'Reflection' => 'any',
            'Phar' => 'any',
            'PDO' => 'any',
            'gd' => 'any',
            'pdo_mysql' => 'any',
            'pdo_sqlite' => 'any',
        );
        
        $optionalExtensions = array(
            'mcrypt' => 'any',
            'curl' => 'any',
            'memcached' => 'any',
            'XCache' => 'any',
            'apc' => 'any',
            'xdebug' => 'any',
            'redis' => 'any',
            'memcache' => 'any',
        );
        
        $requiredPHPVersion = '5.2.0';
        
        // errors count
        $errors = 0;
        
        if ($installer->config->requiredPHPVersion)
            $requiredPHPVersion = $installer->config->requiredPHPVersion;
        
        $this -> getFeatureRef('installer.environment.phpversion', $requiredPHPVersion);
            
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
        
        $this -> getFeatureRef('installer.environment.req-exts', $requiredExtensions);
        
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

        $this -> getFeatureRef('installer.environment.opt-exts', $optionalExtensions);
        
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

        $this -> getFeatureRef('installer.environment.requirements', $requirements);

        // requirements met, ready for next step
        if (!$errors)
            $this -> installer -> enableNextStep();
        
        $this -> template -> push('requirements', $requirements);
        $this -> installer -> template = 'environment';
    }
}
