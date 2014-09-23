<?php
/**
 * Entry of user premium account information (includes payment etc.)
 * 
 * @package Panthera\premiumModule\account
 * @author Damian Kęska
 */

class userPremiumAccount extends pantheraFetchDB
{
    protected $_tableName = 'premium_user';
    protected $_constructBy = array(
        'id', 'array',
    );
    
    protected $_joinColumns = array(
        array('LEFT JOIN', 'premium_type', array('premiumid' => 'premiumid'), array('title' => 'premiumTitle', 'groupid' => 'groupid'))
    );
    
    protected $_unsetColumns = array(
        'premiumTitle', 'groupid',
    );
    
    protected $__userObject = null;
    
    /**
     * Remove premium features from user account
     * 
     * @author Damian Kęska
     * @return mixed
     */
    
    public function delete()
    {
        $this -> deactivate(True);
        return parent::delete();
    }
    
    /**
     * Connect a new account to premium features
     * 
     * @param array $array Array of database columns and values
     * @author Damian Kęska
     * @return mixed
     */
    
    public static function create($array)
    {
        $premium = new premiumAccountType('premiumid', $array['premiumid']);
        
        if (!$premium -> exists())
            return False;
        
        $user = new pantheraUser('id', $array['userid']);
        
        if (!$user -> exists())
            return False;
        
        $array['active'] = intval($array['active']);
        
        if ($array['active'])
        {
            $user -> joinGroup($premium -> groupid);
            $array['activationdate'] = date('Y-m-d H:i:s');
        }
        
        $array['userid'] = intval($array['userid']);
        
        if (!isset($array['requestdate']))
            $array['requestdate'] = date('Y-m-d H:i:s');
        
        return parent::create($array);
    }
    
    /**
     * Return user pantheraUser object
     * 
     * @author Damian Kęska
     * @return pantheraUser
     */
    
    public function getUser()
    {
        if ($this -> __userObject === null)
            $this -> __userObject = new pantheraUser('id', $this -> userid);
        
        return $this -> __userObject;
    }

    public function getExpirationTime()
    {
        return date_calc_diff(time(), $this -> expires);
    }
    
    /**
     * Get premium package that offers same features and have maximum expiration time
     * 
     * @param pantheraUser|int|string $user Panthera user login, id or object
     * @param premiumAccountType|int $premium Premium package type object or premiumid
     * @param string $match (Optional) Match package by groupid (can match multiple packages which offer same features) or by premiumid
     * @param bool $returnObject Return result as object
     * @return string Returns date of latest package or object if $returnObject = true
     */
    
    public static function getLastPackageExpirationDate($user, $premium, $match='groupid', $returnObject=false, $active=true)
    {
        if (!is_numeric($user))
        {
            if (is_string($user))
                $user = new pantheraUser('login', $user);
            
            if (!is_object($user))
                throw new InvalidArgumentException('Invalid $user argument in getLastPackageExpirationDate(). Expected login, userid or pantheraUser object.', 1);

            $user = $user -> id;
        }
        
        if (!is_object($premium))
            $premium = new premiumAccountType('premiumid', $premium);
        
        if (!$premium -> exists())
            throw new InvalidArgumentException('Premium type not found with selected $premium = ' .$premium. ' id', 2);
        
        $filter = new whereClause;
        $filter -> add('AND', 'userid', '=', $user);
        
        if ($match == 'groupid')
            $filter -> add('AND', 'groupid', '=', $premium -> groupid);
        else
            $filter -> add('AND', 'premiumid', '=', $premium -> premiumid);
        
        if ($active)
            $filter -> add('AND', 'active', '=', 1);
        
        $premiums = userPremiumAccount::fetchAll($filter, 0, 1, 'expires', 'DESC');
        
        if ($premiums)
        {
            if ($returnObject)
                return $premiums[0];
            
            return $premiums[0] -> expires;
        }
        
        return false;
    }
    
    /**
     * Deactivate a premium account
     * 
     * @param bool $force (Optional) Force deactivate account (be careful with this option)
     * @param bool $forceLeaveGroup (Optional) Force leave the group (be careful with this option)
     * @author Damian Kęska
     */
    
    public function deactivate($force=False, $forceLeaveGroup=False)
    {
        $user = new pantheraUser('id', $this -> userid);
        
        if (!$this -> active and !$force)
        {
            $this -> panthera -> logging -> output('Premium is already disabled, you can use $force switch to force activate', 'premiumAccounts');
            return false;
        }
        
        // search for other premium packages owned by this user to check if we can kick out from selected group
        $filter = new whereClause;
        $filter -> add('AND', 'userid', '=', $this -> userid);
        $filter -> add('AND', 'groupid', '=', $this -> groupid);
        $filter -> add('AND', 'id', '!=', $this -> id);
        $filter -> add('AND', 'active', '=', 1);
        $filter -> add('AND', 'starts', '<', DB_TIME_NOW);
        //$filter -> add('AND', 'expires', '>', $this -> expires);
        
        $premiums = userPremiumAccount::fetchAll($filter, 0, 1);
        
        // don't allow leaving group if user has other premium package bought 
        if ($premiums and !$forceLeaveGroup)
        {
            $this -> panthera -> logging -> output('There are still other premiums packages, not leaving a group', 'premiumAccounts');
            return True;
        }
        
        if ($user -> exists())
        {
            $user -> leaveGroup(intval($this -> groupid));
            $user -> save();
        }
        
        return true;
    }

    /**
     * Activate/deactivate trigger
     * 
     * @param bool|int|string &$input Input value, true or false
     * @return null
     */

    public function activeFilter(&$input)
    {
        $oldValue = $this -> _data['active'];
        
        // new object
        if (!isset($this -> _data['active']))
            return $input;
        
        // if we want to activate and we cannot, return to old value || if we want to deactivate and we cannot, let's return to old value
        if (($input and !$this -> activate()) || (!$input and !$this -> deactivate()))
            $input = $oldValue;
        
        if ($input)
            $this->awaiting_activation = false;
    }
    
    /**
     * Activate a premium account, add user to premium group
     * 
     * @param bool $force (Optional) Force activate account (be careful with this option)
     * @author Damian Kęska
     */
    
    public function activate($force=False)
    {
        if (!strtotime($this -> expires))
        {
            $this -> panthera -> logging -> output('Expiration date is in past, cannot activate this account', 'premiumAccounts');
            return False;
        }
        
        if ($this -> active and !$force and !$this -> requiresstart)
        {
            $this -> panthera -> logging -> output('Premium account already activated, you can use $force switch to force activate', 'premiumAccounts');
            return false;
        }
        
        if ((strtotime($this -> starts)+3600) > time())
        {
            $this -> requiresstart = true;
            $this -> panthera -> logging -> output('Premium account cannot be activated when it\'s start time is in future far than 1 hour', 'premiumAccounts');
            return true;
        }
        
        $premium = panthera::getModel('premiumAccountType') -> premiumid($this -> premiumid);
        
        if (!$premium -> exists())
        {
            $this -> panthera -> logging -> output('Weird thing, the premium type does not exists, but user entry exists', 'premiumAccounts');
            return false;
        }
        
        // join a premium group
        $user = new pantheraUser('id', $this -> userid);
        
        if (!$user -> exists())
        {
            $this -> panthera -> logging -> output('Premium user does not exists', 'premiumAccounts');
            return false;
        }
        
        $this -> activationdate = date('Y-m-d H:i:s');
        $this -> requiresstart = False;
        $user -> joinGroup(intval($this -> groupid));
        
        $user -> save();
        $this -> save();
        
        return True;
    }
}

/**
 * Types of premmium accounts - "premium_type" table
 * 
 * @package Panthera\premiumModule\account
 * @author Damian Kęska
 */

class premiumAccountType extends pantheraFetchDB
{
    protected $_tableName = 'premium_type';
    protected $_constructBy = array(
        'id', 'array', 'premiumid'
    );
    protected $_idColumn = 'premiumid';
}