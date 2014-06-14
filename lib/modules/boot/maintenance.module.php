<?php
/**
 * Site maintenance module
 *
 * @package Panthera\core\modules\boot
 * @license GNU Lesser General Public License 3, see license.txt
 * @author Damian Kęska
 */

/**
 * Site maintenance module
 *
 * @package Panthera\core\modules\boot
 * @author Damian Kęska
 */


class maintenanceModule extends pantheraClass
{

    /**
     * Main function
     *
     * @return null
     */

    public function __construct()
    {
        parent::__construct();

        if (getUserRightAttribute($this->panthera->user, 'can_see_maintenance_site') or defined('SKIP_MAINTENANCE_CHECK'))
        {
            return false;
        }

        $title = $this -> panthera -> config -> getKey('site.maintenance.title', array(
            'polski' => 'Przerwa techniczna',
            'english' => 'Site maintenance'
        ), 'array', 'site.maintenance');

        $message = $this -> panthera -> config -> getKey('site.maintenance.message', array(
            'polski' => 'Przykro nam, ale obecnie na stronie trwają prace konserwacyjne. Prosimy spróbować później',
            'english' => 'We are sorry, but the site is under maintenance now. Please try again later.'
        ), 'array', 'site.maintenance');

        $this -> panthera -> template -> push('title', $this -> panthera -> locale -> selectStringFromArray($title));
        $this -> panthera -> template -> push('message', $this -> panthera -> locale -> selectStringFromArray($message));
        $this -> panthera -> template -> push('site_title', $this -> panthera -> locale -> selectStringFromArray($this -> panthera -> config -> getKey('site_title')));
        $this -> panthera -> template -> push('user', $this -> panthera -> user);

        try {
            $this -> panthera -> logging -> output('Looking for a site-maintenance.tpl template in current template', 'maintenanceModule');
            $this -> panthera -> template -> display('site-maintenance.tpl');
        } catch (Exception $e) {
            $this -> panthera -> logging -> output('Looking for a site-maintenance.tpl template in "_system"', 'maintenanceModule');
            $this -> panthera -> template -> display('site-maintenance.tpl', False, False, '', '_system');
        }

        pa_exit();
    }
}