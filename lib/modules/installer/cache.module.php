<?php
/**
 * Cache pre-configuration for pantheraInstaller
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Cache pre-configuration for pantheraInstaller
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */


class cacheInstallerControllerSystem extends installerController
{
    /**
     * Get cache modules list
     *
     * @return array
     */

    public function getCacheList()
    {
        $cacheList = array(
            'xcache' => False,
            'apc' => False,
            'memcached' => False,
            'redis' => False,
            'files' => True,
            'db' => True,
        );

        // files cache should be available all time
        $preffered = 'files';

        // check for requirements for built-in caching methods
        if (extension_loaded('xcache'))
        {
            $cacheList['xcache'] = True;
            $preffered = 'xcache';
        }

        if (extension_loaded('apc'))
        {
            $cacheList['apc'] = True;
            $preffered = 'apc';
        }

        if (extension_loaded('memcached'))
        {
            $cacheList['memcached'] = True;
            $preffered = 'memcached';
        }

        if (extension_loaded('redis'))
            $cacheList['redis'] = True;

        return array(
            'list' => $cacheList,
            'preffered' => $preffered,
        );
    }

    /**
     * Check cache module
     *
     * @param string $module Caching module name eg. apc or xcache, files
     * @return Exception|bool Returns True or Exception object
     */

    public function checkCacheModule($module)
    {
        try {
            @include_once getContentDir('modules/cache/varCache_' .$module. '.module.php');

            $className = 'varCache_' .$module;

            if (!class_exists($className))
                throw new Exception('Class "' .$className. '" does not exists', 847);

            $object = new $className($this -> panthera);
            $object -> exists('test');

        } catch (Exception $e) {
            return $e;
        }

        return True;
    }

    /**
     * Main function
     *
     * @feature installer.cache.validate &Exception|bool string Modify multiple validation results
     * @feature installer.cache.list &Exception|bool Modify cache list
     * @return null
     */

    public function display()
    {
        $this -> panthera -> locale -> loadDomain('cache');

        // Detection of APC, XCache and Memcached.
        $t = $this -> getCacheList();
        $this -> getFeatureRef('installer.cache.list', $t);

        if (isset($_POST['cache']) and isset($_POST['varCache']))
        {
            if (isset($t['list'][$_POST['cache']]) and isset($t['list'][$_POST['varCache']]))
            {
                foreach (array($_POST['cache'], $_POST['varCache']) as $cacheName)
                {
                    $validate = $this -> checkCacheModule($cacheName);
                    $this -> getFeatureRef('installer.cache.validate', $validate, $cacheName);

                    if (is_object($validate))
                    {
                        ajax_exit(array(
                            'status' => 'failed',
                            'message' => slocalize('Cache module "%s" reported a error "%s"', 'cache', $cacheName, $validate -> getMessage()),
                        ));
                    } elseif ($validate === false) {
                        ajax_exit(array(
                            'status' => 'failed',
                            'message' => slocalize('Cache module "%s" returned bad code, no error message available', 'cache', $cacheName),
                        ));
                    }
                }


                $this -> panthera -> config -> setKey('cache_type', $_POST['cache'], 'string');
                $this -> panthera -> config -> setKey('varcache_type', $_POST['varCache'], 'string');
                $this -> installer -> enableNextStep();

                ajax_exit(array(
                    'status' => 'success',
                ));

            } else
                ajax_exit(array(
                    'status' => 'failed',
                ));
        }

        // if cache is already set - enable next step
        if ($this -> panthera -> config -> getKey('cache_type', $t['preffered'], 'string') and $this -> panthera -> config -> getKey('varcache_type', $t['preffered'], 'string'))
            $this -> installer -> enableNextStep();

        $this -> panthera -> template -> push(array(
            'cache' => $this -> panthera -> config -> getKey('cache_type'),
            'varCache' => $this -> panthera -> config -> getKey('varcache_type'),
            'cache_list' => $t['list'],

        ));

        $this -> installer -> template = 'cache';
    }
}