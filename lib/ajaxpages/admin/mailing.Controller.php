<?php
/**
 * Manage mailing system
 *
 * @package Panthera\core\components\mailing
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Mailing system pageController
 *
 * @package Panthera\core\components\mailing
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class mailingAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Mail server settings, mass mailing, single mail sending', 'mailing'
    );

    protected $permissions = array(
        'admin.mailing' => array('Mailing configuration', 'mailing'),
    );

    protected $actionuiTitlebar = array(
        'editTemplate' => array('Mailing templates editor', 'mailing'),
    );

    /**
     * Send's a mail from data stored in GET
     *
     * @return null
     */

    public function sendMailFromTemplateAction()
    {
        $data = unserialize(base64_decode($_GET['data']));
        mailMessage::sendMail($data['template'], true, $data['from'], $data['recipients'], array(), null, null, $data['language']);

        ajax_exit(array(
            'status' => 'success',
            'message' => localize('Sent', 'mailing'),
        ));
    }

    /**
     * Send one or more e-mails
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */

    public function sendAction()
    {
        if (isset($_GET['template']))
        {
            if (!$this -> panthera -> varCache)
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('varCache not configured, but required'),
                ));

            $this -> panthera -> varCache -> set('pa-login.system.loginkey', array(
                'key' => generateRandomString(128),
                'userID' => $this -> panthera -> user -> id,
            ), 120);

            // send through self-proxy to catch PHP fatal errors
            $http = new httplib;
            $key = $this -> panthera -> varCache -> get('pa-login.system.loginkey');
            $data = base64_encode(serialize($_REQUEST));
            $response = $http -> get(pantheraUrl('{$PANTHERA_URL}/_ajax.php?_bypass_x_requested_with&_system_loginkey=' .$key['key']. '&display=mailing&cat=admin&action=sendMailFromTemplate&data=' .$data));
            $http -> close();

            $r = json_decode($response, true);

            if ($r)
                ajax_exit($r);
            else
                ajax_dump($response, true);
        }

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
     * @feature admin.mailing.groups $groups List of groups to display
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public function selectAction()
    {
        $groups = pantheraGroup::listGroups();
        $groupsTpl = array();

        $this -> getFeatureRef('admin.mailing.groups', $groups);

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
     * Editing mail template
     *
     * @return null
     */

    public function editTemplateAction()
    {
        $tpl = new mailTemplate('template', $_GET['tpl']);
        $language = $_GET['language'];

        if (!$this -> panthera -> locale -> exists($language))
            $tpl = False;

        if (!$tpl or !$tpl -> exists())
        {
            $this -> panthera -> template -> push('notfound', true);
            $this -> panthera -> template -> display('mailing.editTemplate.tpl');

            pa_exit();
        } else {
            if ($_POST)
            {
                if (isset($_POST['content']['text']))
                    $tpl -> setContent(str_replace('-&gt;', '->', $_POST['content']['text']), $language, 'plain');

                if (isset($_POST['topic']))
                    $tpl -> setTopic($language, $_POST['topic']);

                if (isset($_POST['content']['html']))
                    $tpl -> setContent(str_replace('-&gt;', '->', $_POST['content']['html']), $language, 'html');

                ajax_exit(array(
                    'status' => 'success',
                ));
            }

            $plain = $tpl -> getContent($language, 'plain');
            $html = $tpl -> getContent($language, 'html');

            // convert to string in case getContent would return false
            if (!$plain) $plain = '';
            if (!$html) $html = '';

            $this -> panthera -> template -> push(array(
                'versions' => array(
                    'text' => $plain,
                    'html' => $html,
                ),
                'mailTemplate' => $tpl,
                'lang' => $language,
                'templateName' => $_GET['tpl'],
                'topic' => $tpl -> getTopic($language),
            ));
        }

        $this -> uiTitlebarObject -> setTitle(slocalize('Mailing templates editor - editing "%s" template', 'mailing', $_GET['tpl']));
        $this -> panthera -> template -> display('mailing.editTemplate.tpl');
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

        ajax_exit(array(
            'status' => 'success',
        ));
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

        $mailAttributes = array(
            'mailing_use_php' => array('value' => (bool)$this -> panthera -> config -> getKey('mailing_use_php', 1, 'bool', 'mailing')),
            'mailing_server' => array('name' => 'Server',  'value' => $this -> panthera -> config -> getKey('mailing_server', '', 'string', 'mailing')),
            'mailing_server_port' => array('name' => 'Port', 'value' => $this -> panthera -> config -> getKey('mailing_server_port', 465, 'int', 'mailing')),
            'mailing_user' => array('name' => 'Login', 'value' => $this -> panthera -> config -> getKey('mailing_user', 'user@example.com', 'string', 'mailing')),
            'mailing_password' => array('name' => 'Password', 'value' => $this -> panthera -> config -> getKey('mailing_password', '', 'string', 'mailing')),
            'mailing_smtp_ssl' => array('name' => 'SSL', 'value' => (bool)$this -> panthera -> config -> getKey('mailing_smtp_ssl', True, 'bool', 'mailing')),
            'mailing_from' => array('value' => $this -> panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing')),
        );

        if (!$this -> panthera -> session -> exists('mailing_last_from'))
            $this -> panthera -> session -> set('mailing_last_from', $this -> panthera -> config -> getKey('mailing_from', 'example@example.com', 'string', 'mailing'));

        $mailTemplates = mailTemplate::getTemplates();

        if ($mailTemplates)
        {
            foreach ($mailTemplates as &$template)
            {
                foreach ($template['files'] as &$file)
                {
                    $file = str_replace(PANTHERA_DIR, '', $file);
                    $file = str_replace(SITE_DIR, '', $file);
                }
            }
        }

        $this -> panthera -> template -> push(array(
            'last_subject' => $this->panthera->session->get('mailing_last_subject'),
            'last_recipients' => $this->panthera->session->get('mailing_last_recipients'),
            'last_body' => $this->panthera->session->get('mailing_last_body'),
            'last_from' => $this->panthera->session->get('mailing_last_from'),
            'mail_attributes' => $mailAttributes,
            'mailTemplates' => $mailTemplates,
            'languages' => $this -> panthera -> locale -> getLocales(),
        ));

        return $this -> panthera -> template -> compile('mailing.tpl');
    }
}