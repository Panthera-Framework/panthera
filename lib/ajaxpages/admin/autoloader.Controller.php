<?php
/**
  * Autoloader list with option to clear cache
  *
  * @package Panthera\core\autoloader
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Autoloader pageController class
  *
  * @package Panthera\core\autoloader
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */
  
class autoloaderAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Autoloader cache'
    );
    
    protected $permissions = array(
        'admin.debug.autoloader' => array('Autoloader cache'),
    );
    
    /**
     * Main, display template function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $items = pantheraAutoloader::updateCache();
            ajax_exit(array(
                'status' => 'success',
                'message' => slocalize('Updated autoloader cache, counting %s items', 'system', count($items)),
            ));
        }

        $this -> panthera -> template -> push('autoloader', $this->panthera->config->getKey('autoloader'));
        return $this -> panthera -> template -> compile('autoloader.tpl');
        
    }    
}