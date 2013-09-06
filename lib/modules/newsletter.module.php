<?php
/**
  * Newsletter module with support for multiple protocols like e-mail (smtp), jabber etc.
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
  * Abstract interface for newsletter Type plugins
  *
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
  * @author Damian Kęska, Mateusz Warzyński
  */

class newsletterManagement
{
    /**
      * Create new newsletter category
      *
      * @param string $title Title of a newsletter
      * @param int $users Initial users count (optional)
      * @return bool 
      * @author Damian Kęska
      */

    public static function create($title, $users=null)
    {
        global $panthera;
    
        // convert to integer, just to be safe
        if ($users != null)
            $users = intval($users);
        else
            $users = 0;
            
        // values for SQL query
        $values = array('title' => trim($title), 'users' => $users, 'attributes' => serialize(array()));
        $SQL = $panthera -> db -> query('INSERT INTO `{$db_prefix}newsletters` (`nid`, `title`, `users`, `attributes`, `created`) VALUES (NULL, :title, :users, :attributes, NOW())', $values);

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
        global $panthera;
        
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
          global $panthera;
          return $panthera->db->getRows('newsletters', $by, $limit, $limitFrom, 'newsletters', $orderBy, $order);
    }
    
    /**
      * Sends a message for a specified user using selected protocol
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function send($user, $message, $topic)
    {
        $f = 'newsletterType_' .$user['type'];
        
        if (class_exists($f))
        {
            $f::send($user['address'], $message, $topic);
        }
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
        global $panthera;
        
        print("Initializing newsletter job...\n");
        
        // job data, unserialized from db column `data`
        $jobData = $job->getData();
        
        // create new newsletter instance to get informations about this newsletter category
        $newsletter = new newsletter('nid', $msg['nid']);
        
        $usersCount = $newsletter -> getUsers(False);
        
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
            $jobData['data']['maxLimit'] = $panthera->config->getKey('cron_newsletter_limit', 15, 'int'); // every server should handle this tiny default
            
        // start from last position
        $users = $newsletter -> getUsers($jobData['data']['offset'], $jobData['data']['maxLimit']);
        
        $panthera->logging->output('cronjob jobname=' .$job->jobname. ', offset=' .$jobData['data']['offset']. ', limit=' .$jobData['data']['maxLimit']. ', usersCount=' .$usersCount, 'newsletter');
        
        // move our position
        $jobData['data']['offset'] = ($jobData['data']['offset']+$jobData['data']['maxLimit']);
        print("Changing offset and saving data...\n");
        
        $job->setData($jobData);
        $job->save(); // just to be sure
        print("Saved...\n");
        
        if (count($users) > 0)
        {
            foreach ($users as $user)
            {
                $userMessage = str_ireplace('{$userName}', $user, pantheraUrl($jobData['data']['message']));
                $userTitle = str_ireplace('{$userName}', $user, pantheraUrl($jobData['data']['title']));
            
                // finally send a user a message
                try {
                    newsletterManagement::send($user, $userMessage, $userTitle);
                } catch (Exception $e) { 
                    $panthera -> logging -> output('Cannot send message: ' .print_r($e, True), 'newsletter');
                }
            }
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
        global $panthera;
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
        global $panthera;
        
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
        global $panthera;
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
	
}

/**
  * Newsletter datatype for sending e-mails
  *
  * @author Damian Kęska
  */

class newsletterType_mail implements newsletterType
{
    /**
      * Validating function, checking if address is correct
      *
      * @param string $address Input address
      * @return mixed 
      * @author Damian Kęska
      */

    public static function validate ($address)
    {
        global $panthera;
        return $panthera -> types -> validate($address, 'email');
    }
    
    /**
      * Send a message
      *
      * @param string $address Receiver address
      * @param string $content Message content
      * @param string $topic Topic title
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function send($address, $content, $topic)
    {
        global $panthera;
        
        $mail = new mailMessage(true);
        $mail -> setSubject($topic);
        $mail -> setFrom($panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing'));
        $mail -> addRecipient(trim($address), 'html');
        return $mail -> send($content);
    }
}

/**
  * Newsletter categories management
  *
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
    
    public function getUsers($offset='', $limitTo='', $orderBy='added', $direction='DESC')
    {
        $LIMIT = '';
    
        if (is_int($offset) and is_int($limitTo))
        {
            $LIMIT = 'LIMIT ' .$offset. ', ' .$limitTo;
        }
        
        if (is_bool($offset) and $offset === False)
        {
            $SQL = $this->panthera->db->query('SELECT count(*) FROM `{$db_prefix}newsletter_users` WHERE `nid` = :nid and `activate_id` = ""', array('nid' => $this->nid));
            $fetch = $SQL -> fetch();
            return intval($fetch['count(*)']);
        } else {
            $SQL = $this->panthera->db->query('SELECT * FROM `{$db_prefix}newsletter_users` WHERE `nid` = :nid AND `activate_id` = "" ORDER BY :orderBy :direction ' .$LIMIT, array('nid' => $this->nid, 'orderBy' => $orderBy, 'direction' => $direction));
            return $SQL -> fetchAll();
        }
        
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
                {
                    $userName = $u -> getName();
                }
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
    
    public function execute($message, $title, $date='')
    {
        // crontab is required for newsletter to work
        $this->panthera->importModule('crontab');
        
        // check if we have any users to send newsletter to
        if (!count($this->getUsers()))
        {
            $this->panthera->logging->output('No users to send message for nid=' .$this->nid, 'newsletter');
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
        
        $jobName = $this->generateJobName($message, $title, $time);
        $data = array('message' => $message, 'title' => $title, 'nid' => $this->nid, 'offset' => 0);

        // create new cronjob
        try {
            crontab::createJob($jobName, array('newsletterManagement', 'cronjob'), $data, '*/1');
        } catch (Exception $e) {
            $this->panthera->logging->output('Cronjob exception: ' .print_r($e, True), 'newsletter');
            return False;
        }

        $job = new crontab('jobname', $jobName);
        
        if (!$job->exists())
        {
            $this->panthera->logging->output('Created cronjob with jobname=' .$jobname.' does not exists', 'newsletter');
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
