<?php
/**
 * Show list of ajax pages
 *
 * @package Panthera\core\adminUI\debug\frontcontrollers
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */


/**
 * Ajaxpages list pageController
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class frontcontrollersAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'List of front controllers', 'ajaxpages'
    );

    protected $permissions = 'admin.debug.frontcontrollers';


    public function display()
    {
        // scan both lib and content
        $files = scandir(SITE_DIR);

        $controllers = array();

        foreach ($files as $file)
        {
            $pathinfo = pathinfo($file);

            if (strtolower($pathinfo['extension']) != 'php')
                continue;

            if (!is_file($file))
                continue;

            $name = basename($file);
            $linked = False;

            if (is_link($file))
            {
                if (stripos(readlink($file), '/frontpages') !== False)
                    $linked = True;
            }


            $controllers[] = array(
                'name' => $name,
                'linked' => $linked,
                'modtime' => date($this -> panthera -> dateFormat, filemtime($file))
            );
        }

        $controllers = $this->panthera->executeFilters('pa.frontcontrollers.list', $controllers);
        $this -> panthera -> template -> push('list', $controllers);

        return $this -> panthera -> template -> compile('frontcontrollers.tpl');
    }

}