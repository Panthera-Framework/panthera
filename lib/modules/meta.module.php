<?php
/**
  * Additional functions for User meta
  *
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

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
        $type = 'u';

        if ($group)
            $type = 'g';

        return static::getTags($tag, $type, $value);
    }

    /**
     * Fetch tags by $tag, $type, $value or $user. Leave none or false to skip filter.
     *
     * @param string $tag (Optional) Tag name to search
     * @param string $type (Optional) Type name eg. "g" for groups, "u" for users
     * @param string $value (Optional) Value
     * @param string $user (Optional) User ID owning selected tag
     * @return array
     */

    public static function getTags($tag=Null, $type=Null, $value=Null, $user=Null, $detailed=False)
    {
        $panthera = pantheraCore::getInstance();

        $w = new whereClause;

        if ($tag !== Null)
            $w -> add('', 'name', '=', $tag);

        if ($tag !== Null)
            $w -> add('AND', 'type', '=', $type);

        if ($value !== Null)
            $w -> add('AND', 'value', '=', serialize($value));

        if ($user !== Null)
            $w -> add('AND', 'userid', '=', $user);

        $wc = $w -> show();
        $where = '';
        
        if ($wc[0])
            $where = 'WHERE ' .$wc[0];
        
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` ' .$where. ' ORDER BY `name` ASC LIMIT 0, 1000;', $wc[1]);

        if ($SQL -> rowCount() > 0)
        {
            $array = $SQL -> fetchAll(PDO::FETCH_ASSOC);

            if ($detailed)
            {
                foreach ($array as $k => &$v)
                    $v['value'] = unserialize($v['value']);

                return $array;
            }

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
        $panthera = pantheraCore::getInstance();

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
        $panthera = pantheraCore::getInstance();
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

    public static function get($type, $name)
    {
        $panthera = pantheraCore::getInstance();
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
     * @param int|string $user
     * @return bool
     * @author Damian Kęska
     */

    public static function remove($type=null, $name=null, $user=null)
    {
        $panthera = pantheraCore::getInstance();
        $where = new whereClause;

        if ($name)
            $where -> add('AND', 'name', '=', $name);

        if ($type)
            $where -> add('AND', 'type', '=', $type);

        if ($user)
            $where -> add('AND', 'userid', '=', $user);

        $s = $where -> show();

        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}metas` WHERE ' .$s[0], $s[1]);
        return (bool)$SQL->rowCount();
    }

    /**
     * Update permissions list from controllers data
     *
     * @param array $array List of controllers with it's parameters (pageController::getControllerAttributes result set)
     * @config panthera.permissions array
     * @return array
     */

    public static function updateListsFromControllers($array)
    {
        $panthera = pantheraCore::getInstance();
        $permissions = array();

        foreach ($array as $controller => $data)
        {
            if ($data['permissions'])
            {
                if (is_string($data['permissions']))
                {
                    // if there is already a translated version
                    if (isset($permissions[$data['permissions']]) and $permissions[$data['permissions']] != $data['permissions'])
                        continue;

                    $permissions[$data['permissions']] = $data['permissions'];

                } elseif (is_array($data['permissions'])) {

                    foreach ($data['permissions'] as $perm => $val)
                    {
                        if (is_int($perm))
                            $perm = $val;

                        // if there is already a translated version
                        if ((isset($permissions[$perm]) and $permissions[$perm] != $perm) or strpos($perm, '{$') !== false)
                            continue;

                        $permissions[$perm] = $val;
                    }
                }
            }

            if ($data['actionPermissions'])
            {
                foreach ($data['actionPermissions'] as $action)
                {
                    if (is_string($action))
                    {
                        // if there is already a translated version
                        if (isset($permissions[$action]) and $permissions[$action] != $action)
                            continue;

                        $permissions[$action] = $action;
                    } elseif(is_array($action)) {

                        foreach ($action as $perm => $val)
                        {
                            if (is_int($perm))
                                $perm = $val;

                            // if there is already a translated version
                            if ((isset($permissions[$perm]) and $permissions[$perm] != $perm) or strpos($perm, '{$') !== false)
                                continue;

                            $permissions[$perm] = $val;
                        }
                    }
                }
            }
        }

        $panthera -> config -> setKey('panthera.permissions', $permissions, 'array', 'meta');
        return $permissions;
    }
}