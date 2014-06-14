<?php
/**
  * Integration with Facebook API, just another easy to use wrapper but integrated with Panthera Framework
  *
  * @package Panthera\modules\social
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
 * Facebook cached group object
 *
 * @package Panthera\modules\social
 * @author Damian Kęska
 */

class FBGroup extends pantheraFetchDB
{
    protected $_tableName = 'fb_groups';
    protected $_idColumn = 'groupid';
    protected $_constructBy = array('groupid', 'name', 'array', 'id');

    public function fillDataFromFacebook()
    {
        $this->panthera->importModule('facebook');

        $facebook = new facebookWrapper();
        $group = $facebook->getGroup($this->__get('groupid'));

        if (isset($group['name']))
        {
            $this->__set('name', $group['name']);
            return True;
        }
    }
}

/**
 * Get all facebook groups from local database
 *
 * @package Panthera\modules\social
 * @param array $by Columns passed to WHERE clause
 * @param $limit SQL Limit
 * @param $limitFrom SQL position (offset)
 * @return bool
 * @author Damian Kęska
 */

function getFBGroups($by, $limit=0, $limitFrom=0)
{
      $panthera = pantheraCore::getInstance();
      return $panthera->db->getRows('fb_groups', $by, $limit, $limitFrom, 'FBGroup', 'members_count', 'DESC');
}

/**
 * Remove facebook group from local database
 *
 * @package Panthera\modules\social
 * @param int $groupId Group id
 * @return bool
 * @author Damian Kęska
 */

function removeFBGroup($groupId)
{
      $panthera = pantheraCore::getInstance();
      $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}fb_groups` WHERE `groupid` = :id', array('id' => $groupId));

      return (bool)$SQL->rowCount();
}

/**
 * Add new facebook group to database
 *
 * @package Panthera\modules\social
 * @param int $ownerID Panthera user ID
 * @param int $membersCount Count of members
 * @param string $name Group name
 * @param int $id Facebook group identificator
 * @return bool
 * @author Damian Kęska
 */

function createFBGroup($id, $name, $membersCount, $ownerID)
{
    $panthera = pantheraCore::getInstance();
    $array = array('groupid' => $id, 'name' => $name, 'members_count' => $membersCount, 'ownerid' => $ownerID);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}fb_groups` (`groupid`, `name`, `members_count`, `ownerid`, `added`) VALUES (:groupid, :name, :members_count, :ownerid, NOW());', $array);
    return (bool)$SQL->rowCount();
}

/**
 * A crontab job
 *
 * @package Panthera\modules\social
 * @param array $msg
 * @return bool
 * @author Damian Kęska
 */

function fbGroupPostMessage($msg)
{
    $panthera = pantheraCore::getInstance();

    // import facebook sdk and wrapper
    $panthera -> importModule('facebook');

    // use custom appid and secret if present (if not it will be '' and ignored by facebookWrapper)
    $facebook = new facebookWrapper($msg['custom_appid'], $msg['custom_secret']);

    if (strlen($msg['custom_proxy']) > 0)
        $facebook -> setProxy($msg['custom_proxy']);

    // restore user session
    $facebook -> sdk -> setAccessToken($msg['token']);

    $group = $facebook -> getGroup($msg['gid']);

    if ($panthera->types->validate($msg['link'], 'url'))
        $group -> post($msg['content'], $msg['link']);
    else
        $group -> post($msg['content']);

    echo "Sent.\n";
}