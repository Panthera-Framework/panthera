<?php
/**
  * Menu panel 
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Menu panel main class
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  */

class frontsidePanel_menu
{
    public function display($data)
    {
        global $panthera;

        $menu = new simpleMenu();
        $menu -> loadFromDB($data['storage']['name']);
        
        if (!$data['template'])
        {
            $data['template'] = 'menu.tpl';
        }

        //$panthera -> template -> push('panelMenu', $menu->show());
        return $panthera -> template -> display('panels/' .$data['template'], True, '', array('panelMenu' => $menu->show()));
    }
}
