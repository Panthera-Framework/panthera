<?php
/**
  * Integration with Facebook API, just another easy to use wrapper but integrated with Panthera Framework
  *
  * @package Panthera\modules\social
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

// include facebook sdk library
include(PANTHERA_DIR. '/share/facebook-php-sdk/src/facebook.php');

/**
  * Facebook wrapper with Panthera features integration
  *
  * @package Panthera\modules\social
  * @author Damian Kęska
  */

class facebookWrapper
{
    protected $panthera, $loggedIn = False;
    public $sdk, $user;

    /**
      * Constructor, integrates with Panthera's database
      *
      * @param string $appid (optional - if not provided there will be selected default one from database)
      * @param string $secret (optional, same as with $appid)
      * @return mixed
      * @author Damian Kęska
      */

    public function __construct($appid='', $secret='')
    {
        global $panthera;
        $this->panthera = $panthera;

        if ($appid == '')
            $appid = $panthera->config->getKey('facebook_appid');

        if ($secret == '')
            $secret = $panthera->config->getKey('facebook_secret');

        $this->sdk = new Facebook(array('appId'  => $appid, 'secret' => $secret));

        if (isset($_GET['code']) and isset($_GET['state']))
        {
            $panthera->session->set('facebook_code', $_GET['code']);
            $panthera->session->set('facebook_state', $_GET['state']);
            $panthera -> logging -> output('facebookWrapper::Detected code=' .$_GET['code']. ' and state=' .$_GET['state']. ' in url', 'facebook');
        }

        $panthera -> logging -> output('facebookWrapper::Using appid=' .$appid. ' and secret=' .$secret, 'facebook');

        $_REQUEST['code'] = $panthera->session->get('facebook_code');
        $_REQUEST['state'] = $panthera->session->get('facebook_state');
    }

    /**
      * Set connection proxy
      *
      * @param string $url
      * @param string $auth Optional proxy authorization
      * @return mixed
      * @author Damian Kęska
      */

    public function setProxy($url, $auth='')
    {
        $this->sdk->CURL_OPTS['CURLOPT_PROXY'] = $url;
        $this->sdk->CURL_OPTS['CURLOPT_SSL_VERIFYPEER'] = False;

        if ($auth != '')
            $this->sdk->CURL_OPTS['CURLOPT_PROXYUSERPWD'] = $auth;

        return True;
    }
    
    /**
      * Check if current user likes specified fanpage
      *
      * @param int $pageID
      * @scope user_likes 
      * @return bool 
      * @author Damian Kęska
      */
    
    public function userLikesPage($pageID)
    {
        $checkIfUserLikePage = $this->sdk->api(array(
            "method"    => "fql.query",
            "query"     => "SELECT page_id FROM page_fan WHERE uid=me() AND page_id=".$pageID
        ));
        
        return sizeof($checkIfUserLikePage);

    }

    /**
      * Check if user is logged in
      *
      * @return bool
      * @author Damian Kęska
      */

    public function isLoggedIn()
    {
        if ($this->loggedIn == True)
            return True;

        try {
            $this -> user = $this->sdk->api('/me');
            $this->loggedIn = True;
            return True;
        } catch (Exception $e) {
            return False;
        }
    }

    /**
      * Login user using built-in Panthera's redirections
      *
      * @param array $scope Array of priviledges
      * @param mixed $redirect Set to false if you want to grab redirect link as a function return, or use "script", "meta" or "header" redirection
      * @return mixed
      * @author Damian Kęska
      */

    public function loginUser($scope, $redirect=False)
    {
        if ($this->sdk->getUser())
        {
            try {
                $this -> user = $this->sdk->api('/me');
                return True;
            } catch (FacebookApiException $e) {
                 $url = $this->sdk->getLoginUrl($scope);
                 $this -> panthera -> logging -> output('facebookWrapper::Redirecting user to url=' .$url, 'facebook');
                 return $this->panthera->template->redirect($url, $redirect);
            }
        } else {
            $url = $this->sdk->getLoginUrl($scope);
            $this -> panthera -> logging -> output('facebookWrapper::Redirecting user to url=' .$url. ' (!getUser())', 'facebook');
            return $this->panthera->template->redirect($url, $redirect);
        }
    }

    /**
      * Clean up the session
      *
      * @return bool
      * @author Damian Kęska
      */

    public function logoutUser()
    {
        // if $redirect is false then it will return the url
        //$logout = $this->panthera->template->redirect($this->sdk->getLogoutUrl(), $redirect);
        $this -> panthera -> logging -> output('facebookWrapper::Calling session destroy, cleaning up...', 'facebook');
        $this->sdk->destroySession();
        $this->panthera->session->remove('facebook_code');
        $this->panthera->session->remove('facebook_state');
        $_REQUEST['code'] = '';
        $_REQUEST['state'] = '';
        return True;
    }

    /**
      * Make an FQL query to Facebook servers
      *
      * @param string $query
      * @return mixed
      * @author Damian Kęska
      */

    public function fql($query)
    {
        $this -> panthera -> logging -> output('facebookWrapper::fql (' .$query. ')', 'facebook');
        return $this->sdk->api(array('method' => 'fql.query', 'query' => $query));
    }

    /**
      * Get current logged-in user groups
      *
      * @return mixed
      * @author Damian Kęska
      */

    public function getUserGroups()
    {
        $userGroups = $this->sdk->api('/me/groups');
        $groups = array();

        foreach ($userGroups['data'] as $key => $value)
            $groups[$value['id']] = $value['name'];

        return $groups;
    }

    /**
      * Get group object, allows posting on group wall and more
      *
      * @param int $gid Group ID (only integer allowed)
      * @return mixed
      * @author Damian Kęska
      */

    public function getGroup($gid)
    {
        // parsing: https://www.facebook.com/groups/243165669123459/
        $groupData = $this->sdk->api('/' .$gid. '/');
        return new facebookGroup($this, $groupData);
    }
}

/**
  * Facebook group object returned by facebookWrapper
  *
  * @package Panthera\modules\social
  * @author Damian Kęska
  */

class facebookGroup
{
    protected $facebook, $groupData;

    /**
      * Constructor
      *
      * @param object $facebook
      * @param mixed $groupData
      * @return mixed
      * @author Damian Kęska
      */

    public function __construct($facebook, $groupData)
    {
        $this->facebook = $facebook;
        $this->groupData = $groupData;
    }

    /**
      * Get all group users (with limit set by facebook)
      *
      * @return mixed
      * @author Damian Kęska
      */

    public function getUsers()
    {
        return $this->facebook->fql('SELECT uid FROM group_member WHERE gid = ' .$this->groupData['id']);
    }

    /**
      * Get group owner
      *
      * @return mixed
      * @author Damian Kęska
      */

    public function getOwner()
    {
        return $this->groupData['owner'];
    }

    /**
      * Allows selecting group meta data as a class property
      *
      * @param string $var Property name
      * @return mixed
      * @author Damian Kęska
      */

    public function __get($var)
    {
        if (array_key_exists($var, $this->groupData))
            return $this->groupData[$var];
    }

    /**
      * Post a message on group's wall
      *
      * @param string $message Plain text message content
      * @param string $link URL address to attach (optional)
      * @param string $picture URL address to picture describing attached link (optional)
      * @param string $linkName Title of attached url (optional)
      * @param string $caption Caption (optional)
      * @param string $description URL description (optional)
      * @return mixed
      * @author Damian Kęska
      */

    public function post($message, $link='', $picture='', $linkName='', $caption='', $description='')
    {
        $data = array('message' => $message);

        // optional data
        if ($link != '')
        {
            $data['link'] = $link;

            if ($picture != '')
                $data['picture'] = $picture;

            if ($linkName != '')
                $data['name'] = $linkName;

            if ($caption != '')
                $data['caption'] = $caption;

            if ($description != '')
                $data['description'] = $description;
        }

        return $this->facebook->sdk->api('/' .$this->groupData['id']. '/feed', 'POST', $data);
    }
}