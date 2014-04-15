<?php
/**
 * Get all input variables listed
 *
 * @package Panthera\core\debug
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */
  
/**
 * Get all input variables listed
 *
 * @package Panthera\core\debug
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
        $this -> panthera -> locale -> loadDomain('debug');

        if (!$this -> panthera -> session -> cookies -> exists('Created'))
            $this -> panthera -> session -> cookies -> set('Created', date($this->panthera->dateFormat), time()+60);
        
        $this -> panthera -> session -> set('Name', 'Damian');
        
        $this -> panthera -> template -> push('cookie', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_COOKIE, True))));
        $this -> panthera -> template -> push('pantheraCookie', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($this->panthera->session->cookies->getAll(), True))));
        $this -> panthera -> template -> push('pantheraSession', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($this->panthera->session->getAll(), True))));
        $this -> panthera -> template -> push('SESSION', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_SESSION, True))));
        $this -> panthera -> template -> push('GET', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_GET, True))));
        $this -> panthera -> template -> push('POST', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_POST, True))));
        $this -> panthera -> template -> push('SERVER', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_SERVER, True))));
        
        return $this -> panthera -> template -> compile('dumpinput.tpl');
    }
    
}