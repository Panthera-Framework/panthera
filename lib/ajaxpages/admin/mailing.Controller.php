<?php
/**
  * Manage mailing system
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Mailing system pageController
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class mailingAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Mail server settings, mass mailing, single mail sending', 'mailing'
    );
    
    protected $permissions = 'can_see_debug';
    
    protected $actionPermissions = array(
        'sendAction' => array('canSendMails')
    );
    
    
    
    /**
     * Send one or more e-mails
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
    
    public function sendAction()
    {
        if (strlen($_POST['body']) < 3)
            ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short')));
    
        if (!$this -> panthera -> types -> validate($_POST['from'], 'email'))
            ajax_exit(array('status' => 'failed', 'message' => localize('Please type a valid e-mail adress in "from" input')));
    
        $exp = explode(',', $_POST['recipients']);
        
        $recipients = array();
        
        foreach ($exp as $recipient)
        {
            if (strpos($recipient, 'group:') !== False)
            {
                $recipient = trim(str_ireplace('group:', '', $recipient));
                
                $group = new pantheraGroup('name', $recipient);
                
                foreach ($group->findUsers() as $user)
                {
                    if (strlen($user['mail']) > 4 )
                        $recipients[] = $user['mail'];
                }
            } elseif(strpos($recipient, 'user:') !== False) {
                $recipient = trim(str_ireplace('user:', '', $recipient));
                $user = new pantheraUser('login', $recipient);
                
                if ($user -> exists()) {
                    if (strlen($user->mail) > 4)
                        $recipients[] = $user->mail;
                }
                
            } else {
                $recipients[] = trim($recipient);
            }
        }
        
        $r = 0;
    
        $mail = new mailMessage();
    
        // subject
        $mail -> setSubject($_POST['subject']);
        $mail -> setFrom($_POST['from']);
    
        // all recipients
        foreach ($recipients as $recipient)
        {
            $recipient = trim($recipient);
        
            // custom e-mail adress
            if ($this -> panthera -> types -> validate($recipient, 'email')) { 
                $mail -> addRecipient($recipient);
                $r++;
            }
        }
    
        if ($r > 0)
        {
            $this -> panthera -> session -> set('mailing_last_from', $_POST['from']);
            $this -> panthera -> session -> set('mailing_last_body', $_POST['body']);
            $this -> panthera -> session -> set('mailing_last_recipients', $_POST['recipients']);
            $this -> panthera -> session -> set('mailing_last_subject', $_POST['subject']);
    
            $send = $mail -> send(pantheraUrl($_POST['body']), 'html');
    
            if ($send)
                ajax_exit(array('status' => 'success', 'message' => localize('Sent', 'mailing').' '.$r.' '.localize('mails', 'mailing')));
            else
                ajax_exit(array('status' => 'failed', 'message' => slocalize('Cannot send mail, please check mailing configuration', 'mailing')));
        }
    
        ajax_exit(array('status' => 'failed', 'message' => localize('Please specify at least one recipient which has email', 'mailing')));
    }


    
    /**
     * Select users and groups as recipients in sending window
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */
    
    public function selectAction()
    {
        $groups = pantheraGroup::listGroups();
        $groupsTpl = array();
    
        foreach ($groups as $group) {
            if (isset($_GET['query']))
            {
                if (stripos($group->name, $_GET['query']) !== False)
                    $groupsTpl[] = array('name' => $group->name);
            } else {
                $groupsTpl[] = array('name' => $group->name);
            }
        }
        
        $w = new whereClause();
        $w -> add( 'AND', 'mail', '!=', '');
        
        if ($_GET['query']) {
            $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase
            $w -> add('AND', 'login', 'LIKE', '%'.$_GET['query']. '%');
            $w -> add('OR', 'full_name', 'LIKE', '%'.$_GET['query']. '%');
        }
        
        $usersTotal = pantheraUser::fetchAll($w, False);
        
        // uiPager
        $uiPager = new uiPager('users', $usersTotal, 10);
        $uiPager -> setActive($_GET['page']);
        $uiPager -> setLinkTemplatesFromConfig('mailing_select.tpl');
        $limit = $uiPager -> getPageLimit();
        
        $users = array();
        $usersData = pantheraUser::fetchAll($w, $limit[1], $limit[0]);
        
        foreach ($usersData as $w) {
            // superuser cant be listed, it must be hidden
            if ($w->acl->superuser and !$this->panthera->user->acl->superuser)
                continue;
    
            if($w -> mail) {
                $users[] = array(
                    'login' => $w -> login, 
                    'name' => $w -> getName(),
                    'avatar' => pantheraUrl($w->profile_picture),
                );
            }
        }
        
        $this -> panthera -> locale -> loadDomain('search');
        
        $sBar = new uiSearchbar('uiTop');
        $sBar -> setQuery($_GET['query']);
        $sBar -> setMethod('GET');
        $sBar -> setAddress('?display=mailing&cat=admin&action=select');
        
        $this -> panthera -> template -> push('callback', htmlspecialchars($_GET['callback']));
        $this -> panthera -> template -> push('groups', $groupsTpl);
        $this -> panthera -> template -> push('users', $users);
        $this -> panthera -> template -> display('mailing_select.tpl');
        pa_exit();
    }

    
    
    /**
     * Save mailing settings
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
    
    protected function mailingUsePHP()
    {
        // permissions check
        $this -> checkPermissions('can_edit_mailing');
    
        // list of allowed fields that can be modified
        $fields = array('mailing_use_php', 'mailing_server', 'mailing_server_port', 'mailing_user', 'mailing_password', 'mailing_smtp_ssl', 'mailing_from');
        
        $_POST['mailing_use_php'] = intval($_POST['mailing_use_php']);
        $_POST['mailing_server_port'] = intval($_POST['mailing_server_port']);
        
        if (!$_POST['mailing_use_php'])
        {
            if(!fsockopen($_POST['mailing_server'], $_POST['mailing_server_port'], $errno, $errstr, 5))
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot connect to mailing server', 'mailing')));
        }
        
        foreach ($_POST as $key => $value)
        {
            if (in_array($key, $fields))
                 // we dont select section here as we bet that those keys already exists and the section will be selected automaticaly
                $this -> panthera -> config -> setKey($key, $value);
        }
        
        $this -> panthera -> config -> save();
    
        ajax_exit(array('status' => 'success'));
    }
    
    
    
    /**
     * Main, display function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */
    
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('mailing');
        $this -> panthera -> config -> loadSection('mailing');
        
        $this -> dispatchAction();
        
        if (isset($_POST['mailing_use_php']))
            $this -> mailingUsePHP();
        
        // send permissions to template
        $this -> panthera -> template -> push('canModifySettings', $this->checkPermissions('can_edit_mailing', True));
        $this -> panthera -> template -> push('canSendMails', $this->checkPermissions('canSendMails', True));
        
        $yn = array(0 => localize('No'), 1 => localize('Yes'));

        $mailAttributes = array();
        $mailAttributes['mailing_use_php'] = array('value' => (bool)$this -> panthera -> config -> getKey('mailing_use_php', 1, 'bool', 'mailing'));
        
        // mailing server
        $mailAttributes['mailing_server'] = array('name' => 'Server',  'value' => $this -> panthera -> config -> getKey('mailing_server', '', 'string', 'mailing'));
        $mailAttributes['mailing_server_port'] = array('name' => 'Port', 'value' => $this -> panthera -> config -> getKey('mailing_server_port', 465, 'int', 'mailing'));
        
        // auth data
        $mailAttributes['mailing_user'] = array('name' => 'Login', 'value' => $this -> panthera -> config -> getKey('mailing_user', 'user@example.com', 'string', 'mailing'));
        $mailAttributes['mailing_password'] = array('name' => 'Password', 'value' => $this -> panthera -> config -> getKey('mailing_password', '', 'string', 'mailing'));
        
        // ssl
        $mailAttributes['mailing_smtp_ssl'] = array('name' => 'SSL', 'value' => (bool)$this -> panthera -> config -> getKey('mailing_smtp_ssl', True, 'bool', 'mailing'));
        
        // From header
        $mailAttributes['mailing_from'] = array('value' => $this -> panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing'));
        
        if (!$this -> panthera -> session -> exists('mailing_last_from'))
            $this -> panthera -> session -> set('mailing_last_from', $this -> panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing'));
        
        $this -> panthera -> template -> push ('last_subject', $this->panthera->session->get('mailing_last_subject'));
        $this -> panthera -> template -> push ('last_recipients', $this->panthera->session->get('mailing_last_recipients'));
        $this -> panthera -> template -> push ('last_body', $this->panthera->session->get('mailing_last_body'));
        $this -> panthera -> template -> push ('last_from', $this->panthera->session->get('mailing_last_from'));
        $this -> panthera -> template -> push ('mail_attributes', $mailAttributes);
        
        return $this -> panthera -> template -> compile('mailing.tpl');
    }
}