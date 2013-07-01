<?php
/**
  * Additional functions for User meta
  *
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
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
