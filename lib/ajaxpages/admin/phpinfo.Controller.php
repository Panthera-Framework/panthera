<?php
/**
 * Show information about PHP
 *
 * @package Panthera\core\adminUI\debug\phpinfo
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Show information about PHP
 *
 * @package Panthera\core\adminUI\debug\phpinfo
 */

class phpinfoAjaxControllerSystem extends pageController
{
    protected $permissions = 'can_see_phpinfo';

    /**
     * void main()
     *
     * @return null
     */

    public function display()
    {
        if ($_GET['action'] == 'iframe')
        {
            phpinfo();
            pa_exit();
        }

        $this -> panthera -> importModule('phpquery');
        ob_start();
        phpinfo();
        $html = ob_get_clean();
        $phpQuery = phpQuery::newDocument($html);
        $body = $phpQuery['body'];
        $this -> panthera -> template -> push('phpinfoContent', $body->html());


        $titlebar = new uiTitlebar(localize('phpinfo', 'settings'));

        $this -> panthera -> template -> display('phpinfo.tpl');
        pa_exit();
    }
}