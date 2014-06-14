<?php
/**
  * Autoloader list with option to clear cache
  *
  * @package Panthera\core\system\autoloader
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GPLv3
  */

/**
  * Autoloader pageController class
  *
  * @package Panthera\core\system\autoloader
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

        $cachedClasses = $this -> panthera -> autoloader;

        foreach ($cachedClasses as $class => &$value)
        {
            if (substr($cachedClasses[$class], 0, 1) == ':')
            {
                $exp = explode(':alias:', $cachedClasses[$class]);
                $f = False;

                if (substr($exp[1], 0, 5) == 'file:')
                {
                    $f = pantheraUrl(substr($exp[1], 5, strlen($exp[1])), false, 'system');

                } elseif (substr($exp[2], 0, 5) == 'file:') {
                    $f = pantheraUrl(substr($exp[2], 5, strlen($exp[2])), false, 'system');
                }

                if ($f and is_file($f))
                    $value = localize('Alias to class', 'debug'). ' ' .$exp[1]. ' ' .localize('from', 'debug'). ' ' .$f;
                else
                    $value = localize('Alias to class'). ' ' .$exp[1];
            }
        }

        $this -> panthera -> template -> push('autoloader', $cachedClasses);
        return $this -> panthera -> template -> compile('autoloader.tpl');

    }
}