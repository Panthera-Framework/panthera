<?php
/**
 * Routing configuration step
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Routing configuration step
 * Installs all routes default to built-in Panthera components like custompages, facebook integration, contact, login etc.
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 */

class routingInstallerControllerSystem extends installerController
{
    /**
     * Main function to display everything
     *
     * @feature installer.routing null Install additional routes
     *
     * @author Damian Keska
     * @return null
     */

    public function display()
    {
        // add routes to modules provided with Panthera
        $this -> panthera -> routing -> map('GET|POST', 'contact', array('front' => 'index.php', 'GET' => array('display' => 'contact')), 'contact');
        $this -> panthera -> routing -> map('GET|POST', 'pu[n:forceNative]?/[*:url_id].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_url_id');
        $this -> panthera -> routing -> map('GET|POST', 'pi[n:forceNative]?/[i:id].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_id');
        $this -> panthera -> routing -> map('GET|POST', 'pq[n:forceNative]?/,[i:unique].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_unique');
        $this -> panthera -> routing -> map('GET|POST', 'facebook/#[*:back]', array('front' => 'index.php', 'GET' => array('display' => 'facebook.connect')), 'facebook_connect');
        $this -> panthera -> routing -> map('GET|POST', 'register', array('front' => 'index.php', 'GET' => array('display' => 'register')), 'register');
        $this -> panthera -> routing -> map('GET|POST', 'login', array('front' => 'pa-login.php'), 'login');
        $this -> panthera -> routing -> map('GET|POST', 'index.[html|py|pyc|rb]', array('redirect' => '', 'code' => 301), 'index-html');
        $this -> panthera -> routing -> map('GET', 'file/[i:fileid]/[*:filename]', array('front' => 'download.php'), 'download');

        $this -> getFeature('installer.routing');

        // generate table log
        $routes = array();

        foreach ($this -> panthera -> routing -> getRoutes() as $route => $data)
            $routes[] = array($route, $data[0], $data[1]);


        $this -> installer -> enableNextStep();
        $this -> panthera -> template -> push('spinnerStepMessage', localize('Installing default URL routes...', 'installer'));
        $this -> panthera -> template -> push('spinnerStepTable', $routes);
        $this -> installer -> template = 'spinnerStep';
    }
}