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

    public static function remove($name, $user, $group=False)
    {
        global $panthera;

        $type = 'u';

        if ($group == True)
            $type = 'g';

        $SQL = $panthera -> db -> query ('DELETE FROM `{$db_prefix}metas` WHERE `name` = :name AND `userid` = :userid AND `type` = :type', array('name' => $name, 'userid' => $user, 'type' => $type));

        return (bool)$SQL->rowCount();
    }
}
