<?php
/**
  * Newsletter module with support for multiple protocols like e-mail (smtp), jabber etc.
  *
  * @package Panthera\modules\newsletter
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
  * Abstract interface for newsletter Type plugins
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  */

interface newsletterType
{
    public static function validate ($address);
    public static function send($address, $content, $topic);
}

/**
  * Newsletters management
  *
  * @package Panthera\modules\newsletter
  * @author Damian Kęska
  */

class newsletterManagement
{
    /**
      * Get all avaliable newsletter mailing methods
      *
      * @return array
      * @author Damian Kęska
      */

    public static function getTypes()
    {
        $types = array();

        foreach (get_declared_classes() as $className)
        {
            if (strpos($className, 'newsletterType_') === 0)
            {
                $types[] = substr($className, 15, strlen($className));
            }
        }

        return $types;
    }

    /**
      * Create new newsletter category
      *
      * @param string $title Title of a newsletter
      * @param int $users Initial users count (optional)
      * @param string $type Default newsletter type eg. mail
      * @return bool
      * @author Damian Kęska
      */

    public static function create($title, $users=null, $type=null)
    {
        $panthera = pantheraCore::getInstance();

        // convert to integer, just to be safe
        if ($users)
            $users = intval($users);
        else
            $users = 0;

        if (!$type)
            $type = 'mail';

        if (!class_exists('newsletterType_' .$type))
            $type = 'mail';

        // values for SQL query
        $values = array(
            'title' => trim($title),
            'users' => $users,
            'attributes' => serialize(array()),
            'created' => DB_TIME_NOW,
            'default_type' => $type
        );

        $query = $panthera -> db -> buildInsertString($values, False, 'newsletters');
        $SQL = $panthera -> db -> query($query['query'], $query['values']);

        return (bool)$SQL->rowCount(); // returns True if any rows was inserted
    }

    /**
      * Remove newsletter category
      *
      * @param mixed $id Newsletter's `nid` number or `title` as a string
      * @return bool
      * @author Damian Kęska
      */

    public static function remove($by, $id)
    {
        $panthera = pantheraCore::getInstance();

        if ($by == 'title')
            $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}newsletters` WHERE `title` = :title', array('title' => intval($id)));
        else
            $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}newsletters` WHERE `nid` = :nid', array('nid' => intval($id)));

        return $SQL -> rowCount(); // return True or False if any row was affected
    }

    /**
      * Search newsletter categories by `id`
      *
      * @param string $by Columns to search eg. array('users' => 2), can be empty
      * @return mixed
      * @author Damian Kęska
      */

    public static function search($by='', $limit=0, $limitFrom=0, $orderBy='nid', $order='DESC')
    {
          $panthera = pantheraCore::getInstance();
          return $panthera->db->getRows('newsletters', $by, $limit, $limitFrom, 'newsletters', $orderBy, $order);
    }

    /**
     * Sends a message for a specified user using selected protocol
     *
     * @param string name
     * @return mixed
     * @author Damian Kęska
     */

    public static function send($user, $message, $topic, $from='', $userObject='', $vars='')
    {
        $f = 'newsletterType_' .$user['type'];

        if (class_exists($f))
            $f::send($user['address'], $message, $topic, $from, $userObject='', $vars='');
    }

    /**
     * Cronjob for newsletter
     *
     * @param object $job crontab object
     * @return mixed
     * @author Damian Kęska
     */

    public static function cronjob($msg, $job)
    {
        $panthera = pantheraCore::getInstance();

        print("Initializing newsletter job...\n");

        // job data, unserialized from db column `data`
        $jobData = $job->getData();

        // create new newsletter instance to get informations about this newsletter category
        $newsletter = new newsletter('nid', $msg['nid']);

        if (!isset($jobData['data']['usersCount']))
        {
            if ($jobData['data']['options']['sendToAllUsers'])
                $usersCount = $newsletter -> getUsers(False, False, 'added', 'DESC', False);
            else
                $usersCount = $newsletter -> getUsers(False);

            $jobData['data']['usersCount'] = $usersCount;
        }

        $usersCount = $jobData['data']['usersCount'];
        
        if (is_array($usersCount))
            $usersCount = count($usersCount);

        if (!isset($jobData['data']['offset']))
            $jobData['data']['offset'] = 0;

        // if all messages were sent we can finish that job
        if (intval($jobData['data']['offset']) > $usersCount)
        {
            $job->count_left = "0";
            $job->save();
            return 'FINISHED';
        }

        if (!isset($jobData['data']['maxLimit']))
            $jobData['data']['maxLimit'] = $panthera->config->getKey('newsletter.cronlimit', 15, 'int', 'newsletter'); // every server should handle this tiny default

        // start from last position
        if ($jobData['data']['options']['sendToAllUsers'])
            $users = $newsletter -> getUsers($jobData['data']['offset'], $jobData['data']['maxLimit'], 'added', 'DESC', False);
        else
            $users = $newsletter -> getUsers($jobData['data']['offset'], $jobData['data']['maxLimit']);
        
        $panthera -> logging -> output('cronjob jobname=' .$job->jobname. ', offset=' .$jobData['data']['offset']. ', limit=' .$jobData['data']['maxLimit']. ', usersCount=' .$usersCount, 'newsletter');

        // move our position
        $jobData['data']['offset'] = ($jobData['data']['offset']+$jobData['data']['maxLimit']);
        print("Changing offset and saving data...\n");

        // progress
        if (!isset($jobData['data']['done']))
            $jobData['data']['done'] = 0;
        
        // add seo links
        $jobData['data']['message'] = pantheraUrl($jobData['data']['message']);
        $jobData['data']['title'] = pantheraUrl($jobData['data']['title']);
        
        
        // send mails to custom users not included in newsletter
        $i = 0;
        
        if ($jobData['data']['customUsersList'])
        {
            $config = array(
                'ajax_url' => $panthera -> config -> getKey('ajax_url'),
            );
            
            foreach ($jobData['data']['customUsersList'] as $userID => $userMail)
            {
                if ($i >= $panthera->config->getKey('newsletter.cronlimit', 15, 'int', 'newsletter'))
                    break;
                
                $i++;
                $user = new pantheraUser('id', $userID);
                
                // site title, description and meta tags in user's native language (if avaliable)
                $config['site_title'] = pantheraLocale::selectStringFromArray($panthera -> config -> getKey('site_title'), $user -> language);
                $config['site_description'] = pantheraLocale::selectStringFromArray($panthera -> config -> getKey('site_description'), $user -> language);
                $config['site_metas'] = pantheraLocale::selectStringFromArray($panthera -> config -> getKey('site_metas'), $user -> language);
                
                // include site configuration variables and user data in message and title
                $userMessage = Tools::parseTemplateVars(Tools::parseTemplateVars($jobData['data']['message'], 'user', $user), 'config', $config);
                $userTitle = Tools::parseTemplateVars(Tools::parseTemplateVars($jobData['data']['title'], 'user', $user), 'config', $config);
                
                newsletterManagement::send(array('address' => $userMail, 'type' => 'mail'), $userMessage, $userTitle, $jobData['data']['from'], $user);
                $panthera -> logging -> output('Sent mail to ' .$userMail, 'newsletter');
                unset($jobData['data']['customUsersList'][$userID]);
            }
            
            if (empty($jobData['data']['customUsersList']) or !$jobData['data']['customUsersList'])
            {
                $job->setData($jobData);
                $job->save();
                return 'FINISHED';
            }
        }

        $job->setData($jobData);
        $job->save(); // just to be sure
        print("Saved...\n");

        if ($users)
        {
            $i=0;
            foreach ($users as $user)
            {
                $i++;
                $userMessage = str_ireplace('{$userName}', $user, pantheraUrl($jobData['data']['message']));
                $userTitle = str_ireplace('{$userName}', $user, pantheraUrl($jobData['data']['title']));

                // finally send user a message
                try {
                    newsletterManagement::send($user, $userMessage, $userTitle, $jobData['data']['from']);
                } catch (Exception $e) {
                    $panthera -> logging -> output('Cannot send message: ' .print_r($e, True), 'newsletter');
                }
            }

            $jobData['data']['done'] += $i;

            $job->setData($jobData);
            $job->save(); // just to be sure
        }
    }

    /**
      * Confirm user's subscription
      *
      * @param string $confirmationKey Confirmation key used to identify subscription
      * @return bool
      * @author Damian Kęska
      */

    public static function confirmUser($confirmationKey)
    {
        $panthera = pantheraCore::getInstance();
        $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `activate_id` = :activate_id', array('activate_id' => $confirmationKey));

        // reset `activate_id` to = "", so the subscription will be marked as confirmed
        if ($SQL -> rowCount() > 0)
        {
            $array = $SQL -> fetch();
            $panthera -> db -> query('UPDATE `{$db_prefix}newsletter_users` SET `activate_id` = "" WHERE `id` = :id', array('id' => $array['id']));

            return True;
        }

        return False;
    }

    /**
      * Remove subscriber using `id`
      *
      * @param int $id
      * @return bool
      * @author Damian Kęska
      */

    public static function removeSubscriber($id)
    {
        $panthera = pantheraCore::getInstance();

        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}newsletter_users` WHERE `id` = :id', array('id' => $id));

        return (bool)$SQL->rowCount();
    }

    /**
      * Remove user's subscription
      *
      * @param string $unsubscribe_id Unsubscribe id key used to identify subscription
      * @return bool
      * @author Damian Kęska
      */

    public static function unsubscribe($unsubscribe_id)
    {
        $panthera = pantheraCore::getInstance();
        $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `unsubscribe_id` = :unsubscribe_id', array('unsubscribe_id' => $unsubscribe_id));

        // reset `activate_id` to = "", so the subscription will be marked as confirmed
        if ($SQL -> rowCount() > 0)
        {
            $array = $SQL -> fetch();
            $panthera -> db -> query('DELETE FROM `{$db_prefix}newsletter_users` WHERE `id` = :id', array('id' => $array['id']));

            return True;
        }

        return False;
    }

    /**
      * Update category elements counter
      *
      * @param string $categoryID eg. 11
      * @return void
      * @author Mateusz Warzyński
      */

    public static function updateUsersCount($categoryID)
    {
        $panthera = pantheraCore::getInstance();
        $SQL = $panthera -> db -> query ('SELECT count(*) FROM `{$db_prefix}newsletter_users` WHERE `nid` = :nid', array('nid' => $categoryID));
        $fetch = $SQL -> fetch(PDO::FETCH_ASSOC);
        $panthera -> db -> query('UPDATE `{$db_prefix}newsletters` SET `users` = :users WHERE `nid` = :categoryID', array('users' => $fetch['count(*)'], 'categoryID' => $categoryID));
    }

}

/**
  * Newsletter datatype for sending e-mails
  *
  * @package Panthera\modules\newsletter
  * @author Damian Kęska
  */

class newsletterType_mail implements newsletterType
{
    protected static $connection = null;

    /**
      * Validating function, checking if address is correct
      *
      * @param string $address Input address
      * @return mixed
      * @author Damian Kęska
      */

    public static function validate ($address)
    {
        $panthera = pantheraCore::getInstance();
        return $panthera -> types -> validate($address, 'email');
    }

    /**
     * Send a message
     *
     * @param string $address Receiver address
     * @param string $content Message content
     * @param string $topic Topic title
     * @param string $from (Optional) Sender address
     * @param object $userObject pantheraFetchDb based object, it's attributes will be used eg. pantheraUser login attribute will be used in {$user.login}
     * @param array $vars (Optional) Associative array of values to insert as {$key} - $value
     * @return mixed
     * @author Damian Kęska
     */

    public static function send($address, $content, $topic, $from='', $userObject='', $vars='')
    {
        $panthera = pantheraCore::getInstance();

        // initialize the connection only once
        if (!self::$connection)
            self::$connection = new mailMessage(true);

        self::$connection -> setSubject($topic);

        if (!$from)
            $from = $panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing');
        
        if (is_object($userObject))
        {
            $content = str_replace('{$user.name}', $userObject -> getName(), $content);
            
            foreach ($userObject -> getData() as $key => $value)
                $content = str_replace('{$user.' .$key. '}', $value, $content);
        }
        
        if (is_array($vars))
        {
            foreach ($vars as $key => $value)
                $content = str_replace('{$' .$key. '}', $value, $content);
        }

        self::$connection -> setFrom($from);
        self::$connection -> addRecipient(trim($address), 'html');
        
        return self::$connection -> send($content);
    }
}

/**
  * Newsletter subscriber data model
  *
  * @package Panthera\modules\newsletter
  * @author Damian Kęska
  */

class newsletterSubscriber extends pantheraFetchDB
{
    protected $_tableName = 'newsletter_users';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array', 'address', 'unsubscribe_id', 'activate_id');
    protected $_meta;

    /**
      * Every subscriber can have it's meta tags
      *
      * @return object|null
      * @author Damian Kęska
      */

    public function getMetas()
    {
        if (!isset($this->_meta))
            $this->_meta = new metaAttributes($this->panthera, 'nletter_u', $this->id, 120);

        return $this->_meta;
    }
}

/**
  * Newsletter categories management
  *
  * @package Panthera\modules\newsletter
  * @author Damian Kęska
  */

class newsletter extends pantheraFetchDB
{
    protected $_tableName = 'newsletters';
    protected $_idColumn = 'nid';
    protected $_constructBy = array('nid', 'array', 'title');

    /**
     * Get all newsletter users as array
     *
     * @return array
     * @author Damian Kęska
     */

    public function getUsers($offset='', $limitTo='', $orderBy='added', $direction='DESC', $fromAllCategories=False)
    {
        $LIMIT = '';

        if (!$direction)
            $direction = 'DESC';

        if (!$orderBy)
            $orderBy = 'added';

        if (is_int($offset) and is_int($limitTo))
            $LIMIT = 'LIMIT ' .$offset. ', ' .$limitTo;
        
        $filter = new whereClause;
        $filter -> add('AND', 'activate_id', '=', '');

        if (!$fromAllCategories)
            $filter -> add('AND', 'nid', '=', $this -> nid);
            
        return newsletterSubscriber::fetchAll($filter, $limitTo, $offset, $orderBy, $direction);
    }


    /**
     * Register user in a current newsletter
     *
     * @param string $address Contact address
     * @param string $type Contact address type eg. e-mail, jabber or any other if supported
     * @param int $userid Userid (optional)
     * @param string $cookied Cookie or session id used to identify user (optional)
     * @param bool $activated by default false, but can be activated immediately
     * @param bool $dontSendConfirmation Send a confirmation message, false by default
     * @throws UnexpectedValueException
     * @return mixed
     * @author Damian Kęska
     */

    public function registerUser($address, $type='', $userid=-1, $cookieid='', $activated='', $dontSendConfirmation='')
    {
        if ($type == '')
            $type = $this->default_type;

        $f = "newsletterType_".$type;

        if (!class_exists($f))
            throw new UnexpectedValueException('Unsupported type "' .$type. '", cannot find class "' .$f. '" to handle that type.');

        if (!$f::validate($address))
            throw new UnexpectedValueException('Invalid address format of type ' .$type);

        // check if address is already registered
        $SQL = $this->panthera->db->query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `address` = :address AND `type` = :type', array('address' => $address, 'type' => $type));

        if ($SQL->rowCount() > 0)
            return True;

        // unsubscribe id should be random
        $unsubscribe_id = md5(rand(9999999, 99999999));
        $activate_id = '';

        if ($activated == False)
            $activate_id = md5(rand(9999999, 99999999));


        $values = array('nid' => $this->nid, 'address' => $address, 'type' => strtolower($type), 'cookieid' => $cookieid, 'userid' => intval($userid), 'unsubscribe_id' => $unsubscribe_id, 'activate_id' => $activate_id);
        $SQL = $this->panthera->db->query('INSERT INTO `{$db_prefix}newsletter_users` (`id`, `nid`, `address`, `type`, `added`, `cookieid`, `userid`, `unsubscribe_id`, `activate_id`) VALUES (NULL, :nid, :address, :type, NOW(), :cookieid, :userid, :unsubscribe_id, :activate_id)', $values);

        $this->panthera->get_options('newsletter_registered', array('address' => $address, 'nid' => $this->nid, 'type' => $type));

        if (!$dontSendConfirmation)
        {
            $m = new $f();
            $content = pantheraLocale::selectStringFromArray($this->panthera->config->getKey('nletter.confirm.content', array('english' => 'Hi, {$userName}. <br>Please confirm your newsletter subscription at {$PANTHERA_URL}/newsletter.php?confirm={$activateKey} <br>Your unsubscribe url: {$PANTHERA_URL}/newsletter.php?unsubscribe={$unsubscribeKey}'), 'array', 'newsletter'));
            $topic = pantheraLocale::selectStringFromArray($this->panthera->config->getKey('nletter.confirm.topic', array('english' => 'Please confirm your newsletter subscription'), 'array', 'newsletter'));
            $userName = 'Guest';

            if ($userid !== -1)
            {
                $u = new pantheraUser('id', $userid);

                if ($u -> exists())
                    $userName = $u -> getName();
            }

            $topic = str_ireplace('{$userName}', $userName,
                     str_ireplace('{$unsubscribeKey}', $unsubscribe_id,
                     str_ireplace('{$activateKey}', $activate_id,
                     pantheraUrl($topic)
            )));

            $content = str_ireplace('{$userName}', $userName,
                     str_ireplace('{$unsubscribeKey}', $unsubscribe_id,
                     str_ireplace('{$activateKey}', $activate_id,
                     pantheraUrl($content)
            )));

            $m -> send($address, $content, $topic);
        }

        return (bool)$SQL->rowCount();
    }

    /**
      * Check if given address exists in current newsletter
      *
      * @param string $address
      * @param string $type Address type (eg. mail, jabber etc.), optional - if not specified will use newsletter's default
      * @return bool
      * @author Damian Kęska
      */

    public function isRegisteredAddress($address, $type='')
    {
        if ($type == '')
            $type = $this->default_type;

        $SQL = $this->panthera->db->query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `address` = :address AND `type` = :type AND `nid` = :nid', array('address' => $address, 'type' => $type, 'nid' => $this->nid));

        return (bool)$SQL->rowCount();
    }

    /**
      * Return user's subscription
      *
      * @param string $address
      * @param string $type Address type (eg. mail, jabber etc.), optional - if not specified will use newsletter's default
      * @return bool
      * @author Damian Kęska
      */

    public function getSubscription($address, $type='')
    {
        if ($type == '')
            $type = $this->default_type;

         $SQL = $this->panthera->db->query('SELECT * FROM `{$db_prefix}newsletter_users` WHERE `address` = :address AND `type` = :type AND `nid` = :nid', array('address' => $address, 'type' => $type, 'nid' => $this->nid));

         return $SQL->fetch();
    }

    /**
      * Check if user registered any address identifing its by cookieid/sessionid or userid (registered users)
      * @param string $type Type of validation - can be cookieid or userid
      * @param string $value Just a value eg. cookieid, sessionid or userid
      * @return bool
      * @author Damian Kęska
      */

    public function checkRegistration($type, $value)
    {
        if ($type == 'cookieid')
            $SQL = $this->panthera->db->query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `cookieid` = :cookieid', array('cookieid' => $value));
        else
            $SQL = $this->panthera->db->query('SELECT `id` FROM `{$db_prefix}newsletter_users` WHERE `userid` = :uid', array('uid' => intval($value)));

        return (bool)$SQL->rowCount();
    }

    /**
     * Add message to newsletter queue
     *
     * @param string $message Content of a message
     * @param string $title Title (mail topic if its a mail)
     * @param mixed $date When to start sending messages, input: formatted date or unix timestamp
     * @return mixed
     * @author Damian Kęska
     */

    public function execute($message, $title, $from='', $options='', $date='', $exceptions=False)
    {
        // crontab is required for newsletter to work
        $this->panthera->importModule('crontab');
        
        if ($options['sendToAllUsers'])
            $usersCount = $this -> getUsers(False, False, 'added', 'DESC', False);
        else
            $usersCount = $this -> getUsers(False);

        // check if we have any users to send newsletter to
        if (!$usersCount and (!$options or !$options['recipientsData']))
        {
            $this->panthera->logging->output('No users selected to send message', 'newsletter');
            
            if ($exceptions)
                throw new Exception(localize('No users selected to send message', 'newsletter'), 2);
            
            return False;
        }

        $time = 0;

        // verify date
        if(is_string($date) and $date)
        {
            $time = strtotime($date);
        } elseif (is_int($date))
            $time = $date;

        // avoid dates in past
        if ($time < 0)
        {
            if ($time < time())
                $time = 0;
        }

        if (!is_array($options))
            $options = array();

        $jobName = $this->generateJobName($message.$from, $title, $time);

        $data = array(
            'message' => $message,
            'from' => $from,
            'title' => $title,
            'nid' => $this->nid,
            'offset' => 0,
            'options' => $options,
            'usersCount' => $usersCount,
            'customUsersList' => array(),
        );
        
        if ($options['recipientsData'])
        {
            $users = array();
            $userFilter = new whereClause;
            
            if (isset($options['recipientsData']['gender']) and $options['recipientsData']['gender'])
                foreach ($options['recipientsData']['gender'] as $gender)
                    $userFilter -> add('OR', 'gender', '=', $gender);
            
            if (isset($options['recipientsData']['city']) and $options['recipientsData']['city'])
                foreach ($options['recipientsData']['city'] as $city)
                    $userFilter -> add('OR', 'city', '=', $city);
            
            /*if (isset($options['recipientsData']['group']) and $options['recipientsData']['group'])
                foreach ($options['recipientsData']['group'] as $group)
                    $userFilter -> add('OR', 'primary_group', '=', $group);*/

            if (isset($options['recipientsData']['group']) and $options['recipientsData']['group'])
            {
                foreach ($options['recipientsData']['group'] as $group)
                {
                    $g = new pantheraGroup('group_id', $group);
                    $users = array_merge($users, $g -> getUsers());
                }
            }
            
            /**
             * Premium accounts support
             */
            
            if (isset($options['recipientsData']['premium']) and $options['recipientsData']['premium'])
            {
                $premium = in_array('premium', $options['recipientsData']['premium']);
                $free = in_array('free', $options['recipientsData']['premium']);
                
                $query = $this -> panthera -> db -> query('SELECT {$db_prefix}users.id, mail, p.`premiumid` FROM `{$db_prefix}users` LEFT JOIN `{$db_prefix}premium_user` as p ON {$db_prefix}users.id = p.userid;');
                $fetch = $query -> fetchAll(PDO::FETCH_ASSOC);
                    
                foreach ($fetch as $record)
                {
                    if ((($premium and $record['premiumid']) or ($free and !$record['premiumid'])) and $record['mail'])
                         $data['customUsersList'][$record['id']] = $record['mail'];
                }
            }
            
            $users = array_merge($users, pantheraUser::fetchAll($userFilter));
            
            foreach ($users as $user)
            {
                if (!$user -> exists() or !$user -> mail)
                    continue;
                
                $data['customUsersList'][$user->id] = $user -> mail;
            }
        }

        // create new cronjob
        try {
            crontab::createJob($jobName, array('newsletterManagement', 'cronjob'), $data, '*/1');
            
        } catch (Exception $e) {
            $this->panthera->logging->output('Cronjob exception: ' .print_r($e, True), 'newsletter');
            
            if ($exceptions)
                throw new Exception('Cronjob exception: ' .$e -> getMessage(), 3);
            
            return False;
        }
        
        $job = new crontab('jobname', $jobName);

        if (!$job->exists())
        {
            $this->panthera->logging->output('Created cronjob with jobname=' .$jobname.' does not exists', 'newsletter');
            
            if ($exceptions)
                throw new Exception('Created cronjob with jobname=' .$jobname.' does not exists', 4);
            
            return False;
        }

        // save custom start time
        if ($time > 0)
        {
            $this->panthera->logging->output('Setting time=' .$time. ' for jobname=' .$job->jobname, 'newsletter');
            $job -> start_time = $time;
            $job -> save();
        }

        return True;
    }

    /**
      * Generate cronjob name
      *
      * @param string $message Content of a message
      * @param string $title Title (mail topic if its a mail)
      * @param mixed $date When to start sending messages, input: formatted date or unix timestamp
      * @return string
      * @author Damian Kęska
      */

    public function generateJobName($message, $title, $time='')
    {
        return 'newsletter_' .$this->nid. '_' .substr(md5($message.$title.$time), 0, 5);
    }
}