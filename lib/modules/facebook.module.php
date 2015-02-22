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
    public $cacheTime = 60; // 60 seconds
    public $queryCache = array();

    /**
      * Constructor, integrates with Panthera's database
      *
      * @param string $appid (optional - if not provided there will be selected default one from database)
      * @param string $secret (optional, same as with $appid)
      * @param string $state Serialized Facebook object
      * @return mixed
      * @author Damian Kęska
      */

    public function __construct($appid='', $secret='', $state='', $skipGetUser=False)
    {
        $panthera = pantheraCore::getInstance();
        $this -> panthera = $panthera;

        if ($appid == '')
            $appid = $panthera->config->getKey('facebook_appid', '', 'string', 'facebook');

        if ($secret == '')
            $secret = $panthera->config->getKey('facebook_secret', '', 'string', 'facebook');

        if ($appid == null or $secret == null)
        {
            $panthera -> logging -> output('Facebook integration wrapper requires "facebook_appid" and "facebook_secret" to be configured', 'facebook');
            throw new Exception('Facebook integration wrapper requires "facebook_appid" and "facebook_secret" to be configured');
        }

        if ($state)
        {
            $panthera -> logging -> output('Trying to restore object state', 'facebook');
            $t = unserialize($state);
            $this -> sdk = $t['sdk'];
            $this -> queryCache = $t['queryCache'];

        } else {
            $this -> sdk = new Facebook(array('appId'  => $appid, 'secret' => $secret));
            $panthera -> logging -> output('Using appid=' .$appid. ' and secret=' .$secret, 'facebook');
        }

        if ($panthera -> session -> exists('facebookToken'))
        {
            $panthera -> logging -> output('Restoring access token from session token=' .$panthera -> session -> get('facebookToken'), 'facebook');
            $this -> sdk -> setAccessToken($panthera->session->get('facebookToken'));
        }

        if (!$skipGetUser)
            $this -> sdk -> getUser();

        $panthera -> addOption('session_save', array($this, 'saveAccessToken'));
    }

    /**
     * Save access token to session
     *
     * @return null
     */

    public function saveAccessToken()
    {
       $this -> panthera -> session -> set('facebookToken', $this->sdk->getAccessToken());
    }
    
    /**
     * Get sharer popup link
     * 
     * @static
     * @param string $address URL address
     * @param string $title Title
     * @param string $summary Summary
     * @param string $image Image url
     * @return string URL address
     */
    
    public static function getSharerLink($address, $title='', $summary='', $image='')
    {
        $url = 'https://www.facebook.com/sharer/sharer.php?s=100&p[url]=' .$address;
        
        if ($title)
            $url .= '&p[title]=' .$title;
        
        if ($summary)
            $url .= '&p[summary]=' .$summary;
        
        if ($image)
            $url .= '&p[images][0]=' .$image;
        
        return $url;
    }

    /**
      * Return serialized Facebook object
      *
      * @return string
      * @author Damian Kęska
      */

    public function serializeState()
    {
        return serialize(array('sdk' => $this->sdk, 'queryCache' => $this->queryCache));
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
        $this -> sdk -> CURL_OPTS['CURLOPT_PROXY'] = $url;
        $this -> sdk -> CURL_OPTS['CURLOPT_SSL_VERIFYPEER'] = False;

        if ($auth != '')
            $this -> sdk -> CURL_OPTS['CURLOPT_PROXYUSERPWD'] = $auth;

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
        $checkIfUserLikePage = $this->api(array(
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
            $this -> user = $this -> sdk -> api('/me');
            $this -> loggedIn = True;
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
      * @param string $baseURL Replace facebook generated redirection url with your's
      * @return mixed
      * @author Damian Kęska
      */

    public function loginUser($scope, $redirect=False, $baseURL=False)
    {
        $this -> cleanURI();

        if ($this -> sdk -> getUser()) {
            return True;
        } else {
            $url = $this->sdk->getLoginUrl(array('scope' => $scope));

            if ($baseURL)
            {
                $base = parse_url($url);
                parse_str($base['query'], $args);
                $url = str_ireplace(urlencode($args['redirect_uri']), urlencode($baseURL), $url);
            }

            $this -> panthera -> logging -> output('facebookWrapper::Redirecting user to url=' .$url, 'facebook');
            return $this->panthera->template->redirect($url, $redirect);
        }
    }

    /**
      * Clean URI
      *
      * @return bool
      * @author Mateusz Warzyński
      */

    public function cleanURI()
    {
        $request = explode("&_=", $_SERVER['REQUEST_URI']);
        $_SERVER['REQUEST_URI'] = $request[0].substr($request[1], 13);

        if (!strpos($_SERVER['REQUEST_URI'], "&_="))
            return True;

        return False;
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
        $this -> panthera -> logging -> output('Calling session destroy, cleaning up...', 'facebook');
        $this -> sdk -> destroySession();
        $this -> panthera -> session -> remove('facebook_code');
        $this -> panthera -> session -> remove('facebook_state');
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
        $this -> panthera -> logging -> startTimer();
        $result = $this -> sdk -> api(array('method' => 'fql.query', 'query' => $query));
        $this -> panthera -> logging -> output('FQL query -> "' .$query. '"', 'facebook');
        return $result;
    }

    /**
      * Make a API query
      *
      * @param string $arg
      * @return mixed
      * @author Damian Kęska
      */

    public function api($arg, $allowCaching=False)
    {
        $this -> panthera -> logging -> startTimer();

        if ($allowCaching and $this -> cacheTime > 0)
        {
            $argID = hash('md4', serialize($arg));

            if (isset($this -> queryCache[$argID]))
            {
                if ($this -> queryCache[$argID]['expiration'] <= time())
                {
                    $this -> panthera -> logging -> output('Updating outdated argid=' .$argID, 'facebook');

                    $result = $this -> sdk -> api($arg);
                    $this -> queryCache[$argID] = array(
                        'result' => $result,
                        'expiration' => time() + $this->cacheTime
                    );
                } else {
                    $this -> panthera -> logging -> output('Received item from facebook cache', 'facebook');
                    $result = $this->queryCache[$argID]['result'];
                }

            } else {
                $this -> panthera -> logging -> output('Cached argid=' .$argID, 'facebook');

                $result = $this -> sdk -> api($arg);

                $this -> queryCache[$argID] = array(
                    'result' => $result,
                    'expiration' => time() + $this->cacheTime
                );
            }


        } else {
            $result = $this -> sdk -> api($arg);
        }

        $this -> panthera -> logging -> output('Finished API request', 'facebook');
        return $result;
    }

    /**
      * Get current logged-in user groups
      *
      * @return mixed
      * @author Damian Kęska
      */

    public function getUserGroups()
    {
        $userGroups = $this -> api('/me/groups');
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
        $groupData = $this -> api('/' .$gid. '/');
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
        return $this -> facebook -> fql('SELECT uid FROM group_member WHERE gid = ' .$this->groupData['id']);
    }

    /**
      * Get group owner
      *
      * @return mixed
      * @author Damian Kęska
      */

    public function getOwner()
    {
        return $this -> groupData['owner'];
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
            return $this -> groupData[$var];
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

        return $this -> facebook -> sdk -> api('/' .$this->groupData['id']. '/feed', 'POST', $data);
    }
}