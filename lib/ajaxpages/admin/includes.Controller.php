<?php
/**
 * List of all included files in current code execution
 *
 * @package Panthera\core\adminUI\debug\includes
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */


/**
 * List of all included files in current code execution
 *
 * @package Panthera\core\adminUI\debug\includes
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class includesAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Included files - List of all included files in current code execution', 'includes'
    );

    protected $permissions = 'admin.debug.includes';

    /**
     * Main, display template function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        $this -> panthera -> locale -> loadDomain('includes');

        $files = get_included_files();
        $this -> panthera -> template -> push('files', $files);

        return $this -> panthera -> template -> compile('includes.tpl');
    }
}