<?php
/**
 * Panthera user management
 *
 * @package Panthera\core\user
 * @author Damian Kęska
 * @license LGPLv3
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
    protected $_constructBy = array('id', 'login', 'full_name', 'last_result', 'array', 'mail', 'jabber');
    protected $attributes; // on this data we will operate
    public $acl;
    protected $_joinColumns = array(
        array('LEFT JOIN', 'groups', array('group_id' => 'primary_group'), array('name' => 'group_name'))
    );
    protected $_unsetColumns = array('created', 'modified', 'mod_time', 'last_result', 'group_name');

    /**
     * Customized constructor that also loads meta attributes
     *
     * @return null
     */

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
     * Check if user is admin
     *
     * @return bool
     */

    public function isAdmin()
    {
        return getUserRightAttribute($this, 'admin');
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

    /**
      * Ban, unban or check ban status
      *
      * @param bool $value Set this value to True or False to ban or unban user
      * @return bool
      * @author Damian Kęska
      */

    public function isBanned($value='')
    {
        if ($value !== '')
            $this -> attributes -> banned = intval((bool)$value);

        return $this -> attributes -> banned;
    }

    /**
      * Return user's login or full name depends on if full name is provided in user profile
      *
      * @param string $getLogin Get user login instead of full name
      * @return string
      * @author Damian Kęska
      */

    public function getName($getLogin=False)
    {
        if ($this->__get('full_name') and !$getLogin)
            return $this->__get('full_name');

        return $this->__get('login');
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

        if (isset($this->_data[$var]))
            return $this->_data[$var];
    }

    public function __set($var, $value)
    {
        if ($var == 'id')
            return False;

        return parent::__set($var, $value);
    }

    /**
      * Save all data back to database, including user attributes
      *
      * @return void
      * @author Damian Kęska
      */

    public function save()
    {
        if ($this -> attributes -> changed())
            $this -> __set('attributes', serialize($this->attributes->listAll()));

        parent::save();
    }

    /**
      * Get user's avatar link
      *
      * @return string
      * @author Damian Kęska
      */

    public function getAvatar()
    {
        if (!$this->__get('profile_picture'))
            return pantheraUrl('{$PANTHERA_URL}/images/default_avatar.png', False, 'frontend');
        else
            return pantheraUrl($this->__get('profile_picture'), False, 'frontend');
    }

    /**
     * Get user's last login history
     *
     * @param string|int $beforeDate (Optional) Get last logins before this date
     * @param string|int $afterDate (Optional) Get last logins after this date
     * @param int $offset (Optional) SQL offset
     * @param int $limit (Optional) SQL limit
     */

    public function getLastLoginHistory($beforeDate='', $afterDate='', $offset=0, $limit=100)
    {
        $where = new whereClause;
        $where -> add('', 'uid', '=', $this -> id);

        if ($beforeDate)
        {
            if (is_string($beforeDate))
                $beforeDate = strtotime($beforeDate);

            $beforeDate = date('Y-m-d G:i:s', $beforeDate);

            $where -> add('AND', 'date', '<', $beforeDate);
        }

        if ($afterDate)
        {
            if (is_string($afterDate))
                $afterDate = strtotime($afterDate);

            $afterDate = date('Y-m-d G:i:s', $afterDate);

            $where -> add('AND', 'date', '>', $afterDate);
        }

        $show = $where -> show();
        $SQL = $this -> panthera -> db -> query('SELECT * FROM `{$db_prefix}users_lastlogin_history` WHERE ' .$show[0]. ' ORDER BY `date` DESC LIMIT ' .intval($offset). ', ' .intval($limit), $show[1]);
        $fetch = $SQL -> fetchAll(PDO::FETCH_ASSOC);

        if ($fetch)
        {
            foreach ($fetch as &$row)
            {
                $row['uid'] = intval($row['uid']);
                $row['retries'] = intval($row['retries']);
                $row['date'] = date($this -> panthera -> dateFormat, strtotime($row['date']));
            }
        }


        return $fetch;
    }

    /**
      * Get user attribute by id, pantheraUser object, login or current logged in user
      *
      * @param string $attribute Attribute name to get eg. id, login
      * @param string|int|pantheraUser $input Input data to find user by (integer input means id, string means login and pantheraUser object means user itself)
      * @return
      * @author Damian Kęska
      */

    public static function getAttribute($attribute, $input='')
    {
        global $panthera;

        if (!$input)
        {
            if ($panthera -> user -> exists())
                return $panthera -> user -> __get($attribute);
        }

        $user = null;

        // get by pantheraUser object
        if ($input instanceof pantheraUser)
            $user = $input;

        // get by login
        if (is_string($input))
            $user = new pantheraUser('login', $input);

        // get by id
        if (is_int($input))
            $user = new pantheraUser('id', $input);

        if ($user)
        {
            if ($user -> exists())
                return $user->__get($attribute);
        }

        return False;
    }

    /**
     * Construct by id or login
     *
     * @param int|string $input
     * @return pantheraUser
     */

    public static function autoConstruct($input)
    {
        if (is_int($input))
            return new pantheraUser('id', $input);
        elseif (is_string($input))
            return new pantheraUser('login', $input);
    }

    /**
     * Remove user from system
     *
     * @return bool
     */

    public function delete()
    {
        $this -> acl -> deleteAll();
        parent::delete();

        return True;
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

        if ($this -> exists())
            $this -> acl = new metaAttributes($this->panthera, 'g', $this->group_id, (bool)$this->cache);
    }

    /**
      * Create a new group (static function)
      *
      * @param string $name Group name
      * @param string $description Optional description
      * @return bool False if group already exists, True if created
      * @exceptions Exception('Group name is too short', 851)
      * @author Damian Kęska
      */

    public static function create($name, $description='')
    {
        global $panthera;

        if (strlen($name) < 3)
            throw new Exception('Group name is too short', 851);

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
        $panthera = pantheraCore::getInstance();

        $g = new pantheraGroup('name', $name);

        if (!$g -> exists())
            return False;

        $panthera -> logging -> output ('Removing all users from "' .$name. '" group', 'pantheraGroup');
        $users = $g -> findUsers();

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
        $panthera = pantheraCore::getInstance();
        return $panthera->db->getRows('groups', $by, $limit, $offset, 'pantheraGroup', $orderBy, $order);
    }

    /**
      * Find all group users
      *
      * @return array
      * @author Damian Kęska
      */

    public function findUsers($limit=0, $offset=0)
    {
        $limitQuery = '';
        $what = '`login`, `id`';

        if ($limit and $offset)
            $limitQuery = ' LIMIT ' .intval($offset). ', ' .intval($limit);
        elseif ($limit === False)
            $what = 'count(*)';

        $SQL = $this -> panthera -> db -> query('SELECT ' .$what. ' FROM `{$db_prefix}users` WHERE `primary_group` = :gid ' .$limitQuery, array('gid' => $this->group_id));

        if ($limit === False) {
            $array = $SQL -> fetch(PDO::FETCH_ASSOC);
            return intval($array['count(*)']);
        }

        return $SQL -> fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Create new user in {$db_prefix}users
 *
 * @return bool
 * @package Panthera\core\user
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

function createNewUser($login, $passwd, $full_name, $primary_group='', $attributes, $language, $mail='', $jabber='', $profile_picture='{$PANTHERA_URL}/images/default_avatar.png', $ip='', $requiresConfirmation=False)
{
    $panthera = pantheraCore::getInstance();

    if ($ip == '')
        $ip = $_SERVER['REMOTE_ADDR'];

    // groups check
    if (!$primary_group)
        $primary_group = 'users';

    if (!$panthera -> locale -> exists($language)) {
        throw new Exception('Selected locale does not exists', 864);
        return False;
    }

    $test = new pantheraGroup('name', $primary_group);

    if (!$test -> exists()) {
        throw new Exception('Selected group does not exists', 865);
        return False;
    }

    $primary_group = $test->group_id;

    if ($mail) {
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            throw new Exception('Incorrect e-mail address', 866);
    }

    if ($jabber) {
        if (!filver_var($jabber, FILTER_VALIDATE_EMAIL))
            throw new Exception('Incorrect jabber address', 867);
    }

    // validate login
    $test = new pantheraUser('login', $login);

    if ($test -> exists()) {
        throw new Exception('User already exists', 863);
        return False;
    }

    $login = trim($login);
    $regexp = $panthera -> get_filters('createNewUser.loginRegexp', '/^[a-zA-Z0-9\-\.\,\+\!]+_?[a-zA-Z0-9\-\.\,\+\!]+$/D');

    if (!preg_match($regexp, $login))
        throw new Exception('Login contains invalid characters', 868);

    // ip address (if entered)
    if ($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP))
            throw new Exception('Invalid IP address, leave empty if not required', 878);
    }

    $array = array(
        'login' => strip_tags($login),
        'passwd' => encodePassword($passwd),
        'full_name' => strip_tags($full_name),
        'primary_group' => $primary_group,
        'attributes' => $attributes,
        'language' => $language,
        'mail' => $mail,
        'jabber' => $jabber,
        'profile_picture' => $profile_picture,
        'ip' => $ip
    );


    $SQL = true;

    if ($requiresConfirmation)
    {
        $confirmationKey = generateRandomString(16);

        $details = array(
            'confirmationKey' => $confirmationKey,
            'login' => $array['login']
        );

        $SQL = $panthera -> db -> query ('INSERT INTO `{$db_prefix}password_recovery` (`id`, `recovery_key`, `user_login`, `date`, `new_passwd`, `type`) VALUES (NULL, :confirmationKey, :login, NOW(), " ", "confirmation");', $details);

        if (!$SQL) {
            $panthera -> logging -> output('Cannot insert confirmation key to database, details: ' .$SQL->errorInfo(), 'users');
            return False;
        }

        $panthera -> config -> loadSection('register');
        $messages = $panthera->config->getKey('register.verification.message', array('english' => 'Hello {$userName}, here is a link to confirm your account '.pantheraUrl('{$PANTHERA_URL}/pa-login.php?ckey=', False, 'frontend').'{$key}&login={$userName}'), 'array', 'register');
        $titles = $panthera->config->getKey('register.verification.title', array('english' => 'Account confirmation'), 'array', 'register');

        if (isset($messages[$language]))
        {
            $message = $messages[$language];
            $title = $titles[$language];
        } elseif (isset($messages['english'])) {
            $message = $messages['english'];
            $title = $titles['english'];
        } else {
            $message = end($messages);
            $title = end($titles);
        }

        $message = str_replace('{$key}', $confirmationKey,
                   str_replace('{$userName}', $array['login'],
                   str_replace('{$userID}', $user->id, pantheraUrl($message))));

        $title =   str_replace('{$key}', $confirmationKey,
                   str_replace('{$userName}', $array['login'],
                   str_replace('{$userID}', $user->id, pantheraUrl($title))));

        $panthera -> importModule('mailing');
        $mailRecovery = new mailMessage();
        $mailRecovery -> setSubject($title);
        $mailRecovery -> addRecipient($array['mail']);
        $mailRecovery -> send($message, 'html');
    }

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}users` (`id`, `login`, `passwd`, `full_name`, `primary_group`, `joined`, `attributes`, `language`, `mail`, `jabber`, `profile_picture`, `lastlogin`, `lastip`) VALUES (NULL, :login, :passwd, :full_name, :primary_group, NOW(), :attributes, :language, :mail, :jabber, :profile_picture, NOW(), :ip);', $array);

    if (!$SQL) {
        $panthera -> logging -> output('Cannot insert new user to users table, details: ' .$SQL->errorInfo(), 'users');
        return False;
    }

    return True;
}

/**
 * Check if user is logged in and if is admin (the second, optional argument)
 *
 * @return bool
 * @package Panthera\core\user
 * @author Damian Kęska
 */

function checkUserPermissions($user=null, $admin=False)
{
    $panthera = pantheraCore::getInstance();

    if (!$user)
        $user = $panthera -> user;

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

    // if user is admin or superuser
    if($user->attributes->admin or $user->attributes->superuser or $user->acl->get('admin') or $user->acl->get('superuser'))
        return True;

    // if not a super user, not an admin and not allowed in current context (attribute == false)
    if (!(bool)$user->acl->get($attribute) and !$user->attributes->admin and !$user->attributes->superuser and !$user->acl->get('superuser') and !$user->acl->get('admin'))
        return False;

    return (bool)$user->acl->get($attribute);
}

/**
 * Meta tags management class
 *
 * @package Panthera\core\metaAttributes
 * @author Damian Kęska
 */

class metaAttributes
{
    protected $_metas = null;
    protected $_changed = array();
    protected $_objectID;
    protected $_panthera;
    protected $_type;
    protected $_cacheID = '';
    protected $panthera;
    protected $overlays = array();

    /**
     * Constructor
     *
     * @param pantheraCore $panthera
     * @param string $type Meta type eg. gallery
     * @param string $objectID Object ID eg. 1 (first image in gallery)
     * @author Damian Kęska
     */

    public function __construct(pantheraCore $panthera, $type, $objectID, $cache=True)
    {
        $this->panthera = $panthera;
        $this->_type = $type;
        $this->_objectID = $objectID;

        // check if cache is avaliable
        if ($cache and $panthera -> cache)
        {
            $this -> _cacheID = 'meta.' .$type. '.' .$objectID;

            if ($panthera -> cache -> exists($this->_cacheID))
                $this->_metas = $panthera -> cache -> get($this->_cacheID);
        }

        if ($this->_metas === null)
        {
            $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` WHERE `userid` = :objectID AND `type` = :type', array('objectID' => $objectID, 'type' => $type));
            $Array = $SQL -> fetchAll(PDO::FETCH_ASSOC);

            if (count($Array) > 0) {
                $this->addFromArray($Array);
            } else {
                $this -> _metas = array();
                $panthera -> logging -> output('No any meta tags found for objectid=' .$objectID. ', type=' .$type, 'metaAttributes');
            }

            // update cache
            if ($this -> _cacheID)
            {
                $panthera -> cache -> set ($this->_cacheID, $this->_metas, 'metaAttributes');
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
                continue;

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

        $this -> panthera -> logging -> output('Loaded meta overlay, counting overall ' .count($this->_metas). ' elements', 'metaAttributes');
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
     * Delete all meta tags connected to this object
     *
     * @author Damian Kęska
     * @return bool
     */

    public function deleteAll()
    {
        $metas = $this -> listAll();
        $this -> panthera -> logging -> output('Removing "' .count($metas). '" keys for ' .$this->_type. ':' .$this->_objectID, 'metaAttributes');

        foreach ($metas as $meta)
            $this -> remove($meta);

        $this -> save();

        return (!count($this -> listAll()));
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
        if (isset($this->overlays[$type.$objectID]) and $forceReload === False)
            return True;

        $Array = null;

        if ($this -> _cacheID)
        {
            $cacheID = 'meta.overlay.' .$type. '.' .$objectID;

            if ($this -> panthera -> cache -> exists($cacheID))
            {
                $Array = $this -> panthera -> cache -> get($cacheID);

                if ($Array === null)
                    $Array = array();

                $this -> panthera -> logging -> output ('Read from cache id=' .$cacheID, 'metaAttributes');
            }
        }

        if ($Array === null)
        {
            $SQL = $this -> panthera -> db -> query ('SELECT * FROM `{$db_prefix}metas` WHERE `type` = :type AND `userid` = :objectID', array('type' => $type, 'objectID' => $objectID));
            $Array = $SQL -> fetchAll (PDO::FETCH_ASSOC);

            if ($this -> _cacheID)
            {
                $this -> panthera -> cache -> set ($cacheID, $Array, 'metaAttributes');
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

                    $metaValues = array(
                        'userid' => $this->_objectID,
                        'type' => $this->_type,
                        'name' => $key
                    );

                    try {
                        $panthera -> db -> query('DELETE FROM `{$db_prefix}metas` WHERE `userid` = :userid AND `type` = :type AND `name` = :name', $metaValues);
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
                    $metaValues = array(
                        'value' => serialize($meta['value']),
                        'userid' => $this->_objectID,
                        'type' => $this->_type,
                        'name' => $key
                    );

                    try {
                        $panthera -> db -> query('UPDATE `{$db_prefix}metas` SET `value` = :value WHERE `userid` = :userid AND `type` = :type AND `name` = :name', $metaValues);
                    } catch (Exception $e) {
                        $panthera -> logging -> output ('Cannot update meta attribute id=' .$meta['metaid']. ', exception=' .$e->getMessage(), 'metaAttributes');
                    }
                }
            }

            // reset array because we already saved all values to database
            $this -> _changed = array();

            // write changes to cache too
            if ($this -> _cacheID)
            {
                $panthera -> cache -> set ($this->_cacheID, $this->_metas, 'metaAttributes');
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