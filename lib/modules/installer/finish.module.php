<?php
/**
 * Final step in pantheraInstaller
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('PANTHERA_INSTALLER'))
    return False;

/**
 * Final step in Panthera Installer
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class finishInstallerControllerSystem extends installerController
{
    protected $requirements = array(
        'appconfig',
    );

    /**
     * Main function
     *
     * @return null
     */

    public function display()
    {
        $config = (array)$this -> appConfig -> config;

        $config = $this -> getFeature('installer.finish.config', $config);

        unset($config['requires_instalation']);
        unset($config['cache_db']);
        unset($config['preconfigured']);

        $config['installed'] = true;
        $config['disable_overlay'] = False;

        // move url variable from app.php to database
        $url = $config['url'];
        unset($config['url']);
        $this -> panthera -> config -> updateConfigCache($config);
        $this -> panthera -> config -> setKey('url', $url, 'string');


        $this -> appConfig -> config = (object)$config;
        $this -> appConfig -> save();

        $this -> panthera -> template -> push('userLogin', $panthera -> user -> login);
        $this -> installer -> template = 'finish';
    }
}