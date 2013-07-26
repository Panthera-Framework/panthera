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
            $this->loadUserMeta();
        }
    }

	/**
	 * Load all acl attributes from `{$db_prefix}metas` table
	 *
	 * @return mixed
	 * @author Damian Kęska
	 */

    protected function loadUserMeta()
    {
        $Array = -1;
    
        // get from cache
        if ($this->cache > 0)
        {
            if ($this->panthera->cache->exists('um.'.$this->__get('id')))
            {
                $Array = $this->panthera->cache->get('um.'.$this->__get('id')); // read from cache if exists
                $this->panthera->logging->output('Loaded usermeta from cache id=um.'.$this->__get('id'), 'pantheraUser');
            }
        }
    
        // if there is no any cache
        if ($Array == -1)
        {
            $SQL = $this->panthera->db->query('SELECT `metaid`, `name`, `value` FROM `{$db_prefix}metas` WHERE `userid` = :userid AND `type` = "u"', array('userid' => $this->__get('id')));
            
            $Array = $SQL->fetchAll();
            
            if ($this->cache > 0 and $Array != -1)
            {
                $this->panthera->cache->set('um.'.$this->__get('id'), $Array, $this->cache);
                $this->panthera->logging->output('Wrote usermeta by select to cache id=um.'.$this->__get('id'), 'pantheraUser');
            }
            
        }
        
        if (count($Array) > 0)
            $this->acl = new _userMeta($Array, $this->id, 'u', $this->panthera, $this->cache);
        else
            $this->acl = new _userMeta('', $this->id, 'u', $this->panthera, $this->cache);

        $this -> panthera -> add_option('session_save', array($this->acl, 'save'));
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

        $salt = md5($this->panthera->config->getKey('salt').$passwd);
        $this->__set('passwd', $salt);
        $this->panthera->logging->output('Changing password for user ' .$this->__get('login'). ', passwd=' .$salt, 'pantheraUser');
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
        if (md5($this->panthera->config->getKey('salt').$passwd) == $this->_data['passwd'])
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

    /**
	 * Save current user to database if data was modified
	 *
	 * @return void
	 * @author Damian Kęska
	 */
	 
	 /*public function save()
	 {
	    parent::save();
	 }*/

    /*public function save()
    {
        if($this->attributes->changed() == True or $this->changed == True)
        {
            $this->panthera->logging->output('pantheraUser::Saving user login=' .$this->__get('login'). ', id=' .$this->__get('id'));

            // user list controls, attributes etc.
            $attributes = serialize($this->attributes->listAll());
            $id = (integer)$this->_data['id']; // one thing we cant change

            $copied = $this->_data; // we have to copy this array to unset some variables we dont want to modify
            unset($copied['id']); // dont want to update `id`
            $copied['attributes'] = $attributes; // update `attribtes`

            // $set[0] will be a query string like `id` = :id, `name` = :name and $set[1] will be values array('id' => 1, 'name' => 'Damien') its a database helper function
            $set = $this->panthera->db->dbSet($copied);
            $set[1]['id'] = $id; // this will be needed for our where clause

            $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}users` SET ' .$set[0]. ' WHERE `id` = :id', $set[1]);
            
            // write to cache
            if ($this->cache > 0)
            {
                $this->panthera->cache->set('u.' .$id, $this->_data, $this->cache);
                $this->panthera->logging->output('Wrote to cache id=u.' .$id, 'pantheraUser');
            }
        }
    }*/
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
    public $acl;

    public function __construct($by, $value)
    {
        parent::__construct($by, $value);
        
        if ($this->exists())
        {
            $this->loadGroupMeta();
        }
    }

	/**
	 * Load all ACL attributes from `{$db_prefix}metas` table
	 *
	 * @return mixed
	 * @author Damian Kęska
	 */

    protected function loadGroupMeta()
    {
        // get from cache
        if ($this->cache > 0)
        {
            if ($this->panthera->cache->exists('gm.'.$this->__get('name')))
            {
                $Array = $this->panthera->cache->get('gm.'.$this->__get('name')); // read from cache if exists
                $this->panthera->logging->output('Loaded usermeta from cache id=gm.'.$this->__get('name'), 'pantheraUser');
            }
        }
        
        // if there is no any cache
        if ($Array != null)
        {
            $SQL = $this->panthera->db->query('SELECT `metaid`, `name`, `value` FROM `{$db_prefix}metas` WHERE `userid` = :userid AND `type` = "gacl"', array('userid' => $this->__get('name')));
            $Array = $SQL->fetchAll();
            
            if ($this->cache > 0)
            {
                $this->panthera->cache->set('um.'.$this->__get('id'), $Array, $this->cache);
                $this->panthera->logging->output('Wrote usermeta to cache id=um.'.$this->__get('id'), 'pantheraUser');
            }
            
        }
        
        if (count($Array) > 0)
            $this->acl = new _userMeta($SQL->fetchAll(), $this->name, 'g', $this->panthera, $this->cache);
        else
            $this->acl = new _userMeta('', $this->name, 'g', $this->panthera, $this->cache);

        $this -> panthera -> add_option('session_save', array($this->acl, 'save'));
    }
}

class _userMeta
{
    protected $_metas, $_changed = array(), $_user, $_panthera, $_type, $_input, $_cache;

    public function __construct($array, $user, $type, $panthera, $_cache=0)
    {
        $this->_user = $user;
        $this->_panthera = $panthera;
        $this->_type = $type;
        $this->_input = $array;
        $this->_cache = $cache;
        
        if (is_array($array))
        {
            foreach ($array as $key => $value)
                $this->_metas[$value['name']] = array('metaid' => $value['metaid'], 'value' => unserialize($value['value']));
        }
    }

    public function listAll()
    {
        $array = array();

        foreach ($this->_metas as $key => $value)
            $array[$key] = $value['value'];

        return $array;
    }

    /**
     * Get user meta value
     *
     * @param user meta key
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
            //$this->_panthera->logging->output('userMeta::Creating key=' .$meta. ', value=' .$value. ' where userlogin=' .$this->_user->login);
            $this->_metas[$meta] = array('value' => $value);
            $this->_changed[$meta] = 'create';
            return True;
        }

        //$this->_panthera->logging->output('userMeta::Updating key=' .$meta. ', value=' .$value. ' where userlogin=' .$this->_user->login);
        $this->_metas[$meta]['value'] = $value;
        $this->_changed[$meta] = True;
    }

    public function set($meta, $value) { return $this->__set($meta, $value); }
    public function get($meta, $value) { return $this->__get($meta); }

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

    public function save()
    {
        global $panthera;

        if ($this->modified())
        {
            foreach ($this->_changed as $key => $value)
            {
                $meta = $this->_metas[$key];

                if ((string)$value == "create")
                {
                    // create new meta key in database
                    $metaValues = array('name' => $key, 'value' => serialize($meta['value']), 'type' => $this->_type, 'userid' => $this->_user);
                    try {$panthera->db->query('INSERT INTO `{$db_prefix}metas` (`metaid`, `name`, `value`, `type`, `userid`) VALUES (NULL, :name, :value, :type, :userid)', $metaValues);} catch (Exception $e) {}
                } else {
                    // update existing meta
                    $metaValues = array('value' => serialize($meta['value']), 'metaid' => $meta['metaid']);
                    try {$panthera->db->query('UPDATE `{$db_prefix}metas` SET `value` = :value WHERE `metaid` = :metaid', $metaValues);} catch (Exception $e) {}
                }
            }

            // reset array because we already saved all values to database
            $this -> _changed = array();
            
            // write to cache
            if ($this->cache > 0)
            {
                $this->panthera->cache->set('um.'.$this->_user, $this->_input, $this->cache);
                $this->panthera->logging->output('Wrote usermeta to cache id=um.'.$this->__get('id'), 'pantheraUser');
            }
        }
    }

}

/**
 * Search for users matching criterium specified in $by (array of values eg. array('language' => 'polski')). Returns array of pantheraUser objects.
 *
 * @return array
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
 * @author Damian Kęska
 */

function userCreateSession($user, $passwd)
{
    global $panthera;

    $hash = md5($panthera->config->getKey('salt').$passwd);
    
    $whereClause = new whereClause();
    $whereClause -> add('', 'login', '=', $user);
    $whereClause -> add('AND', 'passwd', '=', $hash);
    
    $usr = new pantheraUser($whereClause);
    
    if ($usr->exists())
    {
        $panthera -> user = $usr;
        $panthera -> session -> uid = $usr->id;
        $usr -> lastlogin = 'NOW()';
        $usr -> save();
        return True;
    }

    return False;
}

/**
  * Create user session by user id
  *
  * @param int $id User id
  * @return bool 
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
 * @author Damian Kęska
 */

function checkUserPermissions($user, $admin=False)
{
    global $panthera;

    if(!$panthera->session->exists('uid'))
        return False;

    if($user == False)
        return False;

    if ($admin == False)
        return True;
    else {
        if ($user->attributes->admin == True or $user->attributes->superuser)
            return True;
        else
            return False;
    }
}

/**
 * Check if user have rights to do action, based on ACL attributes and user attributes
 *
 * @return bool
 * @author Damian Kęska
 */

function getUserRightAttribute($user, $attribute)
{
    /*echo $attribute." = ";
    var_dump($user->attribute($attribute));

    echo ", user->attribute($attribute) = ";
    var_dump($user->attribute($attribute));

    echo ", user->acl->superuser = ";
    var_dump ($user->acl->superuser);
    echo "<br><br>";*/

    // if user has blocked attribute and not a superuser
    if ((string)$user->acl->get($attribute) == 'blocked' AND !$user->attributes->superuser)
        return False;

    // if not a super user, not an admin and not allowed in current context (attribute == false)
    if (!(bool)$user->acl->get($attribute) AND !$user->attributes->admin AND !$user->attributes->superuser)
        return False;

    // if user is admin or superuser
    if($user->attributes->admin or $user->attributes->superuser)
        return True;

    return (bool)$user->acl->get($attribute);
}

/**
  * Check if user is logged in
  *
  * @return bool
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
  * @package Panthera\modules\core
  * @author Damian Kęska
  */

class metaAttributes
{
    protected $_metas = array(), $_changed = array(), $_objectID, $_panthera, $_type, $_cache, $panthera;

    public function __construct($panthera, $type, $objectID)
    {
        $this->panthera = $panthera;
        $this->_type = $type;
        $this->_objectID = $objectID;
        
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` WHERE `userid` = :objectID AND `type` = :type', array('objectID' => $objectID, 'type' => $type));
        
        if ($SQL -> rowCount() > 0)
        {
            $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($fetch as $meta)
            {
                unset($meta['userid']);
                unset($meta['type']);
                $this->_metas[$meta['name']] = $meta;
                unset($this->_metas[$meta['name']]['name']);
                $this->_metas[$meta['name']]['value'] = unserialize($meta['value']);
            }
        } else
            $panthera -> logging -> output('No any meta tags found for search: objectid=' .$objectid. ', type=' .$type, 'meta');
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
            $array[$key] = $value['value'];

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
            $this->_metas[$meta] = array('value' => $value);
            $this->_changed[$meta] = 'create';
            return True;
        }

        $this->_metas[$meta]['value'] = $value;
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

    public function save()
    {
        global $panthera;

        if ($this->modified())
        {
            foreach ($this->_changed as $key => $value)
            {
                $meta = $this->_metas[$key];

                if ((string)$value == "create")
                {
                    // create new meta key in database
                    $metaValues = array('name' => $key, 'value' => serialize($meta['value']), 'type' => $this->_type, 'userid' => $this->_objectID);
                    try {$panthera->db->query('INSERT INTO `{$db_prefix}metas` (`metaid`, `name`, `value`, `type`, `userid`) VALUES (NULL, :name, :value, :type, :userid)', $metaValues);} catch (Exception $e) {}
                } else {
                    // update existing meta
                    $metaValues = array('value' => serialize($meta['value']), 'metaid' => $meta['metaid']);
                    try {$panthera->db->query('UPDATE `{$db_prefix}metas` SET `value` = :value WHERE `metaid` = :metaid', $metaValues);} catch (Exception $e) {}
                }
            }

            // reset array because we already saved all values to database
            $this -> _changed = array();
            
            // write to cache
            /*if ($this->cache > 0)
            {
                $this->panthera->cache->set('um.'.$this->_user, $this->_input, $this->cache);
                $this->panthera->logging->output('Wrote usermeta to cache id=um.'.$this->__get('id'), 'pantheraUser');
            }*/
        }
    }

}
