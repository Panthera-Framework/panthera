<?php
/**
 * Last login history saving module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */

/**
 * Last login history saving module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class lastloginhistoryModule extends pageController
{
    /**
     * Initialize module
     * 
     * @return null
     */
    
    public function initialize($controllerObject)
    {
        $this -> panthera -> add_option('login.success', array($this, 'checkLastLogin'));
    }
    
    /**
     * Hooked function
     * 
     * @hooked login.success
     * @return null
     */
    
    public function checkLastLogin($u)
    {
        // check entries count
        $count = $this -> panthera -> db -> query('SELECT count(*) FROM `{$db_prefix}users_lastlogin_history` WHERE `uid` = :uid', array(
            'uid' => $u -> id,
        ));
        
        $fetch = $count -> fetch(PDO::FETCH_ASSOC);

        // remove outdated history entries
        if ($fetch['count(*)'] >= intval($this -> panthera -> config -> getKey('login.history', 5, 'int', 'pa-login')))
            $this -> panthera -> db -> query('DELETE FROM `{$db_prefix}users_lastlogin_history` WHERE `uid` = :uid ORDER BY `date` ASC LIMIT 1;', array(
                'uid' => $u -> id,
            ));
        
        $location = '';
        
        if (function_exists('geoip_region_by_name'))
        {
            $t = geoip_country_name_by_name($_SERVER['REMOTE_ADDR']);
            
            if ($t)
                $location = $t;
        }
        
        // add current 
        $this -> panthera -> db -> insert('users_lastlogin_history', array(
            'hashid' => hash('md4', $u->id.time()),
            'uid' => $u -> id,
            'useragent' => $_SERVER['HTTP_USER_AGENT'],
            'system' => $this -> panthera -> session -> clientInfo -> os,
            'browser' => $this -> panthera -> session -> clientInfo -> browser,
            'retries' => $u -> attributes -> get('loginFailures'),
            'location' => $location,
            'date' => DB_TIME_NOW,
        ));
    }
}
