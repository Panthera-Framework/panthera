<?php
/**
 * ACL listing
 *
 * @package Panthera\core\acl
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */
 
/**
 * ACL listing
 *
 * @package Panthera\core\acl
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */

class permissionsListAjaxControllerSystem extends pageController
{
    protected $permissions = 'admin';
    protected $uiTitlebar = array(
        'Permissions list', 'acl'
    );
    
    /**
     * Main action
     * 
     * @return null
     */
    
    public function display()
    {
        $this -> panthera -> config -> loadOverlay('meta');
        
        $permissionsLocalized = array();
        
        foreach ($this -> panthera -> listPermissions() as $permission => $value)
        {
            if (is_array($value))
                $value = localize($value[0], $value[1]);
            
            $permissionsLocalized[$permission] = $value;
        }
        
        $var_dump = r_dump($this -> panthera -> listPermissions());
        $this -> panthera -> template -> push('var_dump', $var_dump);
        $this -> panthera -> template -> push('permissions', $permissionsLocalized);
        return $this -> panthera -> template -> compile('permissionsList.tpl');
    }
}
