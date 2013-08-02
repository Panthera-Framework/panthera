<?php
/**
  * Additional functions for User meta
  *
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
global $panthera;
  
// cosmetics, so here are predefined permissions
$panthera -> addPermission('can_see_users_table', localize('Can see other profiles (admin panel)'));
$panthera -> addPermission('can_see_system_info', localize('Can view system informations (admin panel)'));
$panthera -> addPermission('can_update_config_overlay', localize('Can change config overlay (admin panel)'));
$panthera -> addPermission('can_update_locales', localize('Can manage system locales', 'messages'));
$panthera -> addPermission('can_update_config_overlay', localize('Can edit site configuration', 'messages'));
$panthera -> addPermission('can_see_system_info', localize('Can see system informations', 'messages'));
$panthera -> addPermission('can_see_debug', localize('Can view debugging informations', 'messages'));
$panthera -> addPermission('can_manage_debug', localize('Can manage debugger system', 'messages'));
$panthera -> addPermission('can_see_debhook', localize('Can view plugins debugger page', 'messages'));
$panthera -> addPermission('can_update_menus', localize('Can update menus', 'messages'));
$panthera -> addPermission('can_see_ajax_pages', localize('Can see index of all ajax pages', 'messages'));
$panthera -> addPermission('can_manage_all_uploads', localize('Can edit and delete existing uploads added by other users', 'messages'));
$panthera -> addPermission('can_delete_own_uploads', localize('Can delete own uploaded files', 'messages'));
$panthera -> addPermission('can_upload_files', localize('Can upload files', 'messages'));
$panthera -> addPermission('can_view_qmsg', localize('Can view quick messages', 'messages'));
$panthera -> addPermission('can_qmsg_manage_all', localize('Can manage all quickMessages elements', 'messages'));
$panthera -> addPermission('can_access_pa', localize('Can login to admin panel', 'messages'));

// TODO: Support for loading permissions from database (for plugins etc.) with cache support
  
class meta
{
    /**
     * Get all users owning specified meta tag
     *
     * @param string $tag Tag name
     * @param bool $group Is it a group? (optional)
     * @param string $value Tag value (optional)
     * @return array
     */

    public static function getUsers($tag, $group=False, $value=False)
    {
        global $panthera;

        $type = 'u';

        if ($group == True)
            $type = 'g';

        if (is_bool($value))
        {
            $SQL = $panthera -> db -> query ('SELECT `userid`, `value` FROM `{$db_prefix}metas` WHERE `name` = :metaname AND `type` = :type', array('metaname' => $tag, 
                                                                                                                                                    'type' => $type));
        } else {
            $SQL = $panthera -> db -> query ('SELECT `userid`, `value` FROM `{$db_prefix}metas` WHERE `name` = :metaname AND `type` = :type AND `value` = :value', array('metaname' => $tag, 
                                                                                                                                                                         'type' => $type, 
                                                                                                                                                                         'value' => serialize($value)));
        }

        if ($SQL -> rowCount() > 0)
        {
            $array = $SQL -> fetchAll();
            $results = array();
            
            foreach ($array as $key => $value)
            {
                $results[$value['userid']] = unserialize($value['value']);
            }

            return $results;
        }

        return array();
    }

    /**
     * Remove tag from user
     *
     * @param string $name Tag name
     * @param int $user User id or group id
     * @param bool $group Is it a group?
     * @return string
     */

    public static function removeAcl($name, $user, $group=False)
    {
        global $panthera;

        $type = 'u';

        if ($group == True)
            $type = 'g';

        $SQL = $panthera -> db -> query ('DELETE FROM `{$db_prefix}metas` WHERE `name` = :name AND `userid` = :userid AND `type` = :type', array('name' => $name, 'userid' => $user, 'type' => $type));

        return (bool)$SQL->rowCount();
    }
    
    /**
      * Adding new meta attribute
      *
      * @param string $name Key
      * @param string $value Value 
      * @param string $type Type
      * @param int $objectid ID (optional)
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function create($name, $value, $type, $objectid=null)
    {
        global $panthera;
        $values = array('name' => $name, 'value' => serialize($value), 'type' => $type, 'userid' => $objectid);
        $SQL = $panthera -> db -> query ('INSERT INTO `{$db_prefix}metas` (`metaid`, `name`, `value`, `type`, `userid`) VALUES (NULL, :name, :value, :type, :userid);', $values);
        return (bool)$SQL->rowCount();
    }

    /**
      * Simply get one record from meta tags table
      *
      * @param string $type
      * @param srting $name
      * @return array 
      * @author Damian Kęska
      */
    
    public function get($type, $name)
    {
        global $panthera;
        $values = array('name' => $name, 'type' => $type);
        $SQL = $panthera -> db -> query ('SELECT `value` FROM `{$db_prefix}metas` WHERE `name` = :name AND `type` = :type', $values);
        
        $result = $SQL->fetch();
        
        if ($SQL->rowCount() > 0)
            return unserialize($result['value']);
            
        return false;
    }
    
    /**
      * Remove record from meta table
      *
      * @param string $type
      * @param string $name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function remove($type, $name)
    {
        global $panthera;
        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}metas` WHERE `name` = :name AND `type` = :type', array('name' => $name, 'type' => $type));
        return (bool)$SQL->rowCount();
    }
}
