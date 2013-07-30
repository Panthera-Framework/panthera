<?php
/**
  * Device selection at boot time
  * 
  * @package Panthera\modules\boot
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

global $panthera;

if (isset($_GET['__switchdevice']) and !defined('DISABLE_DEVICES_SWITCH'))
{
    $devices = $panthera -> get_filters('boot.devices', array('mobile', 'tablet', 'desktop'));
    
    // check if device exists on list
    if (in_array($_GET['__switchdevice'], $devices))
    {
        // check if template for that device exists
        if (array_key_exists($_GET['__switchdevice']. '_template', $panthera -> template -> template))
        {
            // force pantheraTemplate to load specified template instead of current one
            $panthera -> logging -> output('Switching template to "' .$panthera -> template -> template [$_GET['__switchdevice']. '_template']. '"', 'boot');
            $panthera -> session -> set('template.force', array($panthera -> template -> name, $panthera -> template -> template [$_GET['__switchdevice']. '_template']));
            $this -> template -> setTemplate( $this -> template -> name );
        } else
            $panthera -> logging -> output('Template "' .$_GET['__switchdevice']. '_template" not found for ' .$panthera->template->name, 'boot');
    } else
        $panthera -> logging -> output ('Unrecognized device type, cannot switch device', 'boot');
}
