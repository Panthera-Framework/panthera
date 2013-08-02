<?php
/**
  * Panthera user management
  * @package Panthera\core\user
  * @author Damian Kęska
  */

/**
 * Panthera User Management Class
 *
 * @package Panthera\core\user
 * @author Damian Kęska
 */

class pantheraUser extends pantheraFetchDB
{
    protected $_tableName = 'users';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'login', 'full_name', 'last_result', 'array');
    protected $attributes; // on this data we will operate
    public $acl;
    protected $changed;

    //public function __construct($id, $panthera)
    public function __construct($by, $value)
    {
        parent::__construct($by, $value);
     
        if ($this->panthera->cacheType('cache') == 'memory' and $this->panthera->db->cache and $this->cache == True)
            $this->cache = $this->panthera->db->cache;
            
        if ($this->exists())
        {
            // user attributes will be avaliable to read and write via $self->attributes->attribute
            $this->attributes = new _arrayObject(@unserialize($this->_data['attributes']));

            // user meta values (permissions)
            $this -> acl = new metaAttributes($this->panthera, 'u', $this->id, $this->cache);
            
            // merge group rights with user rights
            $this -> acl -> loadOverlay('g', $this->_data['primary_group']);
        }
    }

	/**
	 * Get user attribute
	 *
	 * @return mixed
	 * @author Damian Kęska
	 */

    public function attribute($var)
    {
        return $this->attributes->__get($var);
    }

    /**
	 * Change user password to specified in first argument
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function changePassword($passwd)
    {
        if (trim($passwd) == '')
            return False;

        $this->__set('passwd', encodePassword($passwd));
        $this->panthera->logging->output('Changing password for user ' .$this->__get('login'). ', passwd=' .$this->__get('passwd'), 'pantheraUser');
        return True;
    }

	/**
	 * Check if specified password in first argument matches user password
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function checkPassword($passwd)
    {
        if (verifyPassword($passwd, $this->passwd))
            return True;

        return False;
    }

    // user attributes will be avaliable via $self->attribute
    public function __get($var)
    {
        if($var == 'attributes')
            return $this->attributes;

        if($var == 'acl')
            return $this->acl;
            
        if($var == 'meta')
            return $this->acl;
            
        if(array_key_exists($var, $this->_data))
            return $this->_data[$var];
    }

    public function __set($var, $value)
    {
        if ($var == 'id')
            return False;

        return parent::__set($var, $value);
    }
}

/**
  * Panthera groups management
  *
  * @package Panthera\core\user\groups
  * @author Damian Kęska
  */

class pantheraGroup extends pantheraFetchDB
{
    protected $_tableName = 'groups';
    protected $_idColumn = 'group_id';
    protected $_constructBy = array('id', 'group_id', 'name', 'array');
    protected $cache = 3600;
    public $acl;
    
    /**
      * Constructor
      *
      * @param string $by
      * @param string $value
      * @return void
      * @author Damian Kęska
      */

    public function __construct($by, $value)
    {
        parent::__construct($by, $value);
        
        if ($this->exists())
        {
            $this -> acl = new metaAttributes($this->panthera, 'g', $this->name, $this->cache);
        }
    }
    
    /**
      * Create a new group (static function)
      *
      * @param string $name Group name
      * @param string $description Optional description
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function create($name, $description='')
    {
        global $panthera;
    
        if (strlen($name) < 3)
            throw new Exception('Group name is too short');

        // check if group already exists
        $g = new pantheraGroup('name', $name);
        
        if ($g->exists())
            return False;
            
        unset($g);
        
        $panthera -> db -> query('INSERT INTO `{$db_prefix}groups` (`group_id`, `name`, `description`) VALUES (NULL, :name, :description)', array('name' => $name, 'description' => $description));
        return True;
    }
    
    /**
      * Remove a group
      *
      * @param string $name
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function remove($name)
    {
        global $panthera;
        
        $g = new pantheraGroup('name', $name);
        
        if (!$g->exists())
            return False;

        $panthera -> logging -> output ('Removing all users from "' .$name. '" group', 'pantheraGroup');
        $users = $g->findUsers();
        
        // remove users from group
        if (count($users) > 0)
        {
            foreach ($users as $user)
            {
                $u = new pantheraUser('id', $user['id']);
                
                if ($name != 'users')
                    $u -> primary_group = 'users';
                else
                    $u -> primary_group = '';
                    
                $u -> save();
                unset($u);
            }
        }
        
        try {
            $panthera -> logging -> output ('Removing group\'s meta tags and entry from group table', 'pantheraGroup');
            $panthera -> db -> query('DELETE FROM `{$db_prefix}groups` WHERE `name` = :name;', array('name' => $name));
            $panthera -> db -> query('DELETE FROM `{$db_prefix}metas` WHERE `type` = "g" AND `userid` = :name', array('name' => $name));
            
            if ($panthera -> cache)
            {
                $panthera -> logging -> output('Cleaning up cache', 'pantheraGroup');
                // remove meta attributes from cache
                $panthera -> cache -> remove('meta.g.' .$name);
                
                // remove group cache
                $panthera -> cache -> remove($panthera->db->prefix. '_groups.s:4:"name";.' .$name);
                $panthera -> cache -> remove($panthera->db->prefix. '_groups.s:2:"id";.' .$g->group_id);
                $panthera -> cache -> remove($panthera->db->prefix. '_groups.s:8:"group_id";.' .$g->group_id);
                
                $panthera -> logging -> output('Cache cleanup done', 'pantheraGroup');
            }
            
            return True;
        } catch (Exception $e) {
            $panthera -> logging -> output('Cannot delete group\'s "' .$name. '" meta and group table entry', 'pantheraGroup');
        }
        
        return False;
    }
    
    /**
      * List groups
      *
      * @param mixed $by
      * @param int $offset
      * @param int $limit
      * @return array of objects 
      * @author Damian Kęska
      */
    public static function listGroups($by='', $offset='', $limit='', $orderBy='group_id', $order='DESC')
    {
        global $panthera;
        return $panthera->db->getRows('groups', $by, $limit, $offset, 'pantheraGroup', $orderBy, $order);
    }
    
    /**
      * Find all group users
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function findUsers()
    {
        $SQL = $this -> panthera -> db -> query('SELECT `login`, `id` FROM `{$db_prefix}users` WHERE `primary_group` = :groupName', array('groupName' => $this->name));
        return $SQL -> fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Search for users matching criterium specified in $by (array of values eg. array('language' => 'polski')). Returns array of pantheraUser objects.
 *
 * @return array
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function getUsers($by, $limit=0, $limitFrom=0)
{
    global $panthera;
    return $panthera->db->getRows('users', $by, $limit, $limitFrom, 'pantheraUser');
}

/**
 * Get user object using it's id
 *
 * @return pantheraUser
 * @author Damian Kęska
 */

function getUserById($id)
{
    return new pantheraUser('id', $id);
}

/**
 * Get current logged in user (if logged in)
 *
 * @return pantheraUser
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function getCurrentUser()
{
    global $panthera;

    $sessionKey = $panthera->config->getKey('session_key');

    if(isset($_SESSION[$sessionKey]['uid']) AND $_SESSION[$sessionKey]['uid'] != False)
    {
        return getUserById($_SESSION[$sessionKey]['uid']);
    }

    return false;
}

/**
 * Create new user in {$db_prefix}users
 *
 * @return bool
 * @package Panthera\core\user
 * @author Mateusz Warzyński
 */

function createNewUser($login, $passwd, $full_name, $primary_group, $attributes, $language, $mail='', $jabber='')
{
    global $panthera;
    $array = array('login' => $login, 'passwd' => $passwd, 'full_name' => $full_name, 'primary_group' => $primary_group, 'attributes' => $attributes, 'language' => $language, 'mail' => $mail, 'jabber' => $jabber, 'profile_picture' => '{$PANTHERA_URL}/images/default_avatar.png');

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}users` (`id`, `login`, `passwd`, `full_name`, `primary_group`, `joined`, `attributes`, `language`, `mail`, `jabber`, `profile_picture`) VALUES (NULL, :login, :passwd, :full_name, :primary_group, NOW(), :attributes, :language, :mail, :jabber, :profile_picture);', $array);

    if ($SQL)
      return True;

    return False;
}

/**
 * Simply remove user by `name`. Returns True if any row was affected
 *
 * @return bool
 * @package Panthera\core\user
 * @author Mateusz Warzyński
 */

function removeUser($login)
{
    global $panthera;
    $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}users` WHERE `login` = :login', array('login' => $login));

    if ($SQL)
        return True;

    return False;
}

/**
 * A simple login method
 *
 * @return bool
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function userCreateSession($user, $passwd)
{
    global $panthera;

    $usr = new pantheraUser('login', $user);
    
    if ($usr->exists())
    {
        if ($usr -> checkPassword($passwd))
        {
            $panthera -> user = $usr;
            $panthera -> session -> uid = $usr->id;
            $usr -> lastlogin = 'NOW()';
            $usr -> save();
            return True;
        }
    }

    return False;
}

/**
  * Create user session by user id
  *
  * @param int $id User id
  * @return bool 
  * @package Panthera\core\user
  * @author Damian Kęska
  */

function userCreateSessionById($id)
{
    global $panthera;

    $user = new pantheraUser('id', $id);
    
    if ($user -> exists())
    {
        $panthera -> session -> uid = $id;
        $panthera -> user = $user;
        return True;
    }
    
    return False;
}

/**
 * Simply logout user
 *
 * @return bool
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function logoutUser()
{
    global $panthera;
    $panthera -> session -> remove ('uid');

    return True;
}

/**
 * Check if user is logged in and if is admin (the second, optional argument)
 *
 * @return bool
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function checkUserPermissions($user, $admin=False)
{
    global $panthera;

    if(!$panthera->session->exists('uid'))
        return False;
        
    if($user == False)
        return False;
        
    if (!$user->exists())
        return False;

    if ($admin == False)
        return True;
    else {
        if ($user->attributes->admin == True or $user->attributes->superuser or $user->acl->get('admin') or $user->acl->get('superuser'))
            return True;
        else
            return False;
    }
}

/**
 * Check if user have rights to do action, based on ACL attributes and user attributes
 *
 * @return bool
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function getUserRightAttribute($user, $attribute)
{
    if (!is_object($user))
        return False;

    // if user has blocked attribute and not a superuser
    if ((string)$user->acl->get($attribute) == '__blocked__' and !$user->attributes->superuser and !$user->acl->get('superuser'))
        return False;

    // if not a super user, not an admin and not allowed in current context (attribute == false)
    if (!(bool)$user->acl->get($attribute) and !$user->attributes->admin and !$user->attributes->superuser and !$user->acl->get('superuser') and $user->acl->get('admin'))
        return False;

    // if user is admin or superuser
    if($user->attributes->admin or $user->attributes->superuser or $user->acl->get('user') or $user->acl->get('superuser'))
        return True;

    return (bool)$user->acl->get($attribute);
}

/**
  * Check if user is logged in
  *
  * @return bool
  * @package Panthera\core\user
  * @author Damian Kęska
  */

function userLoggedIn()
{
    global $panthera;
    
    if (!is_object($pantera->user))
        return False;
    
    return $panthera -> user -> exists();
}

/**
  * Meta tags management class
  *
  * @package Panthera\core\user
  * @author Damian Kęska
  */

class metaAttributes
{
    protected $_metas = null;
    protected $_changed = array();
    protected $_objectID;
    protected $_panthera;
    protected $_type;
    protected $_cache;
    protected $_cacheID = '';
    protected $panthera;
    protected $overlays = array();
    
    /**
      * Constructor
      *
      * @param string $type Meta type eg. gallery
      * @param string $objectID Object ID eg. 1 (first image in gallery)
      * @author Damian Kęska
      */

    public function __construct($panthera, $type, $objectID, $cache)
    {
        $this->panthera = $panthera;
        $this->_type = $type;
        $this->_objectID = $objectID;
        $this->_cache = $cache;
        
        // check if cache is enabled
        if ($this -> _cache > 0 and $panthera -> cache)
        {
            $this -> _cacheID = 'meta.' .$type. '.' .$objectID;
        
            if ($this->panthera->cache->exists($this->_cacheID))
            {
                $cache = $this->panthera->cache->get($this->_cacheID);
                $usedCache = True;

                if ($cache === null or empty($cache))
                {
                    $this->_metas = array();
                } else {
                    $this->_metas = $this->addFromArray($cache); // read from cache if exists
                    $this->panthera->logging->output('Loaded meta from cache id=' .$this->_cacheID, 'metaAttributes');
                }
            }
        } else
            $panthera -> logging -> output ('Cache disabled for meta type=' .$type. ', objectid=' .$objectID, 'metaAttributes');
            
        if ($this->_metas === null and is_int($cache))
        {
            $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` WHERE `userid` = :objectID AND `type` = :type', array('objectID' => $objectID, 'type' => $type));
            $Array = $SQL -> fetchAll(PDO::FETCH_ASSOC);
            
            if (count($Array) > 0)
            {
                $this->addFromArray($Array);
            } else {
                $this -> _metas = array();
                $panthera -> logging -> output('No any meta tags found for objectid=' .$objectID. ', type=' .$type, 'metaAttributes');
            }
            
            // update cache
            if ($this -> _cache > 0 and $panthera -> cache)
            {
                $panthera -> cache -> set ($this->_cacheID, $this->_metas, $this->cache);
                $panthera -> logging -> output ('Wrote meta to cache id=' .$this->_cacheID, 'metaAttributes');
            }
            
        }
        
        if ($this->_metas == null)
            $this->_metas = array();

        $panthera -> add_option('session_save', array($this, 'save'));
    }
    
    /**
      * Add data from array
      *
      * @param array $Array
      * @param string $overlay name
      * @return void 
      * @author Damian Kęska
      */
    
    protected function addFromArray ($Array, $overlay='', $overwrite=True)
    {
        if ($this->_metas == null)
            $this->_metas = array();
            
        foreach ($Array as $key => $meta)
        {
            if ($meta['name'] == null)
                continue;
        
            // dont overwrite old keys
            if (isset($this->_metas[$meta['name']]) and $overwrite == False )
            {
                continue;
            }
            
            if (!isset($meta['name']))
                $meta['name'] = $key;
        
            // looks complicated, yeah? we dont need to store some variables, so we can unset it
            unset($meta['userid']);
            unset($meta['type']);
            
            $this->_metas[$meta['name']] = $meta;
            unset($this->_metas[$meta['name']]['name']);
            
            // value
            if (is_bool($meta['value']))
                $this->_metas[$meta['name']]['value'] = $meta['value'];
            else
                $this->_metas[$meta['name']]['value'] = unserialize($meta['value']);
            
            // overlay or not an overlay (empty string)
            $this->_metas[$meta['name']]['overlay'] = $overlay;
        }
        
        $this->panthera->logging->output('Loaded meta overlay, counting overall ' .count($this->_metas). ' elements', 'metaAttributes');
    }
    
    /**
      * List all loaded tags
      *
      * @return array 
      * @author Damian Kęska
      */

    public function listAll()
    {
        $array = array();

        foreach ($this->_metas as $key => $value)
        {
            // skip items marked for removal
            if ($this->_changed[$key] == 'remove')
                continue;
                
            $array[$key] = $value['value'];
        }
        
        return $array;
    }

    /**
     * Get meta value
     *
     * @param string $meta Key name
     * @return mixed
     * @author Damian Kęska
     */

    public function __get($meta)
    {
        if (array_key_exists((string)$meta, $this->_metas))
            return $this->_metas[$meta]['value'];

        return NuLL;
    }
    
    /**
      * Mark meta value for removal
      *
      * @param string $meta name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function remove($meta)
    {
        if (array_key_exists((string)$meta, $this->_metas))
        {
            // can't remove variables from overlays eg. group meta
            if ($this->_metas[$meta]['overlay'] != '')
                return False;
        
            $this->_changed[$meta] = 'remove';
            return True;
        }
        
        return False;
    }

    /**
     * Set user meta value
     *
     * @param meta key and meta value
     * @return void
     * @author Damian Kęska
     */

    public function __set($meta, $value)
    {
        // creating new keys
        if (!array_key_exists($meta, $this->_metas))
        {
            $this->_metas[$meta] = array('value' => $value, 'overlay' => '');
            $this->_changed[$meta] = 'create';
            return True;
        }

        $this->_metas[$meta]['value'] = $value;
        $this->_metas[$meta]['overlay'] = ''; // save in user meta, not in overlay even if this variable was read from overlay
        $this->_changed[$meta] = True;
    }

    public function set($meta, $value) { return $this->__set($meta, $value); }
    public function get($meta) { return $this->__get($meta); }

    /**
     * Check if any modification was done on user meta values
     *
     * @param optional takes meta name as parametr, if not parameter given will return status of modification of all records
     * @return true
     * @author Damian Kęska
     */

    public function modified($meta='')
    {
        if ($meta != '')
            return (bool)$this->_changed[$meta];

        return (bool)count($this->_changed);
    }
    
    /**
      * Load other set of meta tags eg. group tags to merge with user tags
      *
      * @param string $type
      * @param string $objectID
      * @param bool $forceReload Force reload tags if already loaded from this overlay
      * @param bool $highPriority If set to true it will overwrite existing tags from other overlays and from main set of tags
      * @return bool true when something was loaded, false when no meta tags loaded
      * @author Damian Kęska
      */
    
    public function loadOverlay($type, $objectID, $forceReload=False, $highPriority=False)
    {
        // overlay already loaded
        if (array_key_exists($type.$objectID, $this->overlays) and $forceReload === False)
            return True;
        
        $Array = null;
            
        if ($this -> _cache > 0 and $this -> panthera -> cache)
        {
            $cacheID = 'meta.' .$type. '.' .$objectID;
            
            if ($this -> panthera -> cache -> exists($cacheID))
            {
                $Array = $this -> panthera -> cache -> get($cacheID);
                
                if ($Array == null)
                    $Array = array();
                
                $this -> panthera -> logging -> output ('Read from cache id=' .$cacheID, 'metaAttributes');
            }        
        }
        
        if ($Array == null)
        {
            $SQL = $this -> panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` WHERE `type` = :type AND `userid` = :objectID', array('type' => $type, 'objectID' => $objectID));
            $Array = $SQL -> fetchAll (PDO::FETCH_ASSOC);
            
            if ($this -> _cache > 0 and $this -> panthera -> cache)
            {
                $this -> panthera -> cache -> set ($cacheID, $Array, $this->_cache);
                $this -> panthera -> logging -> output ('Wrote to cache id=' .$cacheID, 'metaAttributes');
            }
        }
        
        if (count($Array) > 0)
        {
            $this->addFromArray($Array, $type.$objectID, $highPriority);
            return True;
        }
        
        return False;
    }
    
    /**
      * Save attributes that were modified
      *
      * @return void 
      * @author Damian Kęska
      */

    public function save()
    {
        global $panthera;

        if ($this->modified())
        {
            foreach ($this->_changed as $key => $value)
            {
                $meta = $this->_metas[$key];
                
                /**
                  * Creating new attribute
                  *
                  * @author Damian Kęska
                  */

                if ((string)$value == "create")
                {
                    // create new meta key in database
                    $metaValues = array('name' => $key, 'value' => serialize($meta['value']), 'type' => $this->_type, 'userid' => $this->_objectID);
                    try {
                        $panthera -> db -> query('INSERT INTO `{$db_prefix}metas` (`metaid`, `name`, `value`, `type`, `userid`) VALUES (NULL, :name, :value, :type, :userid)', $metaValues);
                    } catch (Exception $e) {
                        $panthera -> logging -> output ('Cannot create meta attribute id=' .$meta['metaid']. ', exception=' .$e->getMessage(), 'metaAttributes');
                    }
                    
                /**
                  * Removing attribute
                  *
                  * @author Damian Kęska
                  */

                } elseif ((string)$value == 'remove') {
                
                    // can't remove variable from overlay
                    if ($meta['overlay'] != '')
                        continue;
                
                    try {
                        $panthera -> db -> query('DELETE FROM `{$db_prefix}metas` WHERE `metaid` = :metaid', array('metaid' => $meta['metaid']));
                        unset($this->_metas[$key]);
                    } catch (Exception $e) {
                        $panthera -> logging -> output ('Cannot remove meta attribute id=' .$meta['metaid']. ', exception=' .$e->getMessage(), 'metaAttributes');
                    }
                
                /**
                  * Updating existing one
                  *
                  * @author Damian Kęska
                  */
                    
                } else {
                
                    // cannot update possibly non-existing keys
                    if ($meta['overlay'] != '')
                        continue;
                
                    // update existing meta
                    $metaValues = array('value' => serialize($meta['value']), 'metaid' => $meta['metaid']);
                    try {
                        $panthera -> db -> query('UPDATE `{$db_prefix}metas` SET `value` = :value WHERE `metaid` = :metaid', $metaValues);
                    } catch (Exception $e) {
                        $panthera -> logging -> output ('Cannot update meta attribute id=' .$meta['metaid']. ', exception=' .$e->getMessage(), 'metaAttributes');
                    }
                }
            }

            // reset array because we already saved all values to database
            $this -> _changed = array();
            
            // write changes to cache too
            if ($this -> _cache > 0 and $panthera -> cache)
            {
                $panthera -> cache -> set ($this->_cacheID, $this->_metas, $this->cache);
                $panthera -> logging -> output ('Saved meta to cache id=' .$this->_cacheID, 'metaAttributes');
            }
            
            // write to cache
            /*if ($this->cache > 0)
            {
                $this->panthera->cache->set('um.'.$this->_user, $this->_input, $this->cache);
                $this->panthera->logging->output('Wrote usermeta to cache id=um.'.$this->__get('id'), 'pantheraUser');
            }*/
        }
    }
}
