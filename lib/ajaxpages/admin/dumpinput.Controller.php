<?php
/**
 * Get all input variables listed
 *
 * @package Panthera\core\adminUI\debug\dumpinput
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Get all input variables listed
 *
 * @package Panthera\core\adminUI\debug\dumpinput
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class dumpinputAjaxControllerCore extends pageController
{
    protected $requirements = array();

    protected $uiTitlebar = array(
        'Input listing', 'settings'
    );

    protected $permissions = array('admin.debug.dumpinput' => array('Input listing', 'setting'), 'admin');

    /**
     * Display debhook site,
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        // test cookie
        if (!$this -> panthera -> session -> cookies -> exists('Created'))
            $this -> panthera -> session -> cookies -> set('Created', date($this->panthera->dateFormat), time()+60);

        $this -> panthera -> session -> set('Name', 'Damian');

        $this -> panthera -> template -> push(array(
            'cookie' => print_r_html($_COOKIE, true),
            'pantheraCookie' => print_r_html($this->panthera->session->cookies->getAll(), true),
            'pantheraSession' => print_r_html($this->panthera->session->getAll(), true),
            'SESSION' => print_r_html($_SESSION, true),
            'GET' => print_r_html($_GET, true),
            'POST' => print_r_html($_POST, true),
            'SERVER' => print_r_html($_SERVER, true),
        ));

        return $this -> panthera -> template -> compile('dumpinput.tpl');
    }

}