<?php
/**
 * User registration module
 *
 * @package Panthera\core\modules\userregistration
 * @author Damian Kęska
 * @license LGPLv3
 */

class userRegistration extends validableForm
{
    public $disabledFields = array(); // eg. array('login')
    public $fieldsSettings = array(
        'login' => array('lengthFrom' => 5, 'lengthTo' => 16),
        'mail' => array('lengthFrom' => 5, 'lengthTo' => 48, 'repeat' => true),
        'passwd' => array('lengthFrom' => 5, 'lengthTo' => 64, 'generate' => false),
        'fullname' => array('lengthFrom' => 2, 'lengthTo' => 64, 'optional' => True)
    );

    // templates
    public $formTemplateEnabled = 'registrationForm.tpl';
    public $formTemplateDisabled = 'registrationForm.closed.tpl';
    public $formName = '';

    /**
     * Check if user verified an e-mail address
     *
     * @param string $key Confirmation key
     * @param bool $validateUser Validate user or just check
     * @author Damian Kęska
     */

    public static function checkEmailValidation($key, $validateUser=False)
    {
        $panthera = pantheraCore::getInstance();

        $query = $panthera -> db -> query('SELECT * FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key AND `type` = "confirmation"', array('key' => $key));

        if ($query -> rowCount() > 0)
        {
            if ($validateUser)
            {
                $panthera -> db -> query('DELETE FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key', array('key' => $key));
            }

            return (True && $validateUser);
        }

        return (False && $validateUser);
    }

    protected function _processFormValidation()
    {
        // if not posting a form
        if (!$this->isPostingAForm())
        {
            return False;
        }
        
        // ===== support for "login" field
        if (!$this->disabledFields['login'])
        {
            // strip html tags
            $this -> source['login'] = strip_tags(trim($this->source['login']));

            if (!$this->source['login'])
            {
                return array(
                    'message' => localize('Please fill login field', 'register'),
                    'code' => 'LOGIN_FILL',
                    'field' => 'login'
                );
            }

            if (strlen($this->source['login']) > $this->fieldsSettings['login']['lengthTo'] or strlen($this->source['login']) <= $this->fieldsSettings['login']['lengthFrom'])
            {
                return array(
                    'message' => localize('Invalid login field length', 'register'),
                    'settings' => $this->fieldsSettings['login'],
                    'code' => 'LOGIN_LENGTH',
                    'field' => 'login'
                );
            }

            $regexp = $this -> panthera -> get_filters('createNewUser.loginRegexp', '/^[a-zA-Z0-9\-\.\,\+\!]+_?[a-zA-Z0-9\-\.\,\+\!]+$/D');

            if (!preg_match($regexp, $this->source['login']))
            {
                return array(
                    'message' => localize('Invalid characters in login, allowed only A-Z, a-z, 0-9, -, +, !, and comma', 'register'),
                    'settings' => $this->fieldsSettings['login'],
                    'code' => 'LOGIN_CHARACTERS',
                    'field' => 'login'
                );
            }
        }


        // ===== User full name field
        if (!$this->disabledFields['fullname'])
        {
            // strip html tags
            $this -> source['fullname'] = strip_tags(trim($this->source['fullname']));

            if (!$this->source['fullname'] and !$this->fieldsSettings['fullname']['optional'])
            {
                return array(
                    'message' => localize('Please enter your full name', 'register'),
                    'code' => 'FULLNAME_FILL',
                    'field' => 'fullname'
                );
            }

            if ($this->source['fullname'])
            {
                if (strlen($this->source['fullname']) > $this->fieldsSettings['fullname']['lengthTo'] or strlen($this->source['fullname']) <= $this->fieldsSettings['fullname']['lengthFrom'])
                {
                    return array(
                        'message' => localize('Invalid fullname length', 'register'),
                        'settings' => $this->fieldsSettings['fullname'],
                        'code' => 'FULLNAME_LENGTH',
                        'field' => 'fullname'
                    );
                }
            }
        }


        // ===== support for "mail" fields
        if (!$this->disabledFields['mail'])
        {
            // trim whitespaces
            $this->source['mail'] = trim($this->source['mail']);
            $this->source['mail_repeat'] = trim($this->source['mail_repeat']);

            if (!$this->source['mail'])
            {
                return array(
                    'message' => localize('Please fill mail field correctly', 'register'),
                    'code' => 'MAIL_FILL',
                    'field' => 'mail'
                );
            }

            if (strlen($this->source['mail']) > $this->fieldsSettings['mail']['lengthTo'] or strlen($this->source['mail']) <= $this->fieldsSettings['mail']['lengthFrom'])
            {
                return array(
                    'message' => localize('Invalid login field length', 'register'),
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_LENGTH',
                    'field' => 'mail'
                );
            }

            if ($this->fieldSettings['mail']['repeat'] && $this->source['mail'] != $this->source['mail_repeat'])
            {
                return array(
                    'message' => localize('Entered e-mail address does not match confirmation e-mail adddress', 'register'),
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_MATCH_FIELDS',
                    'field' => 'mail_repeat'
                );
            }

            if (!filter_var($this->source['mail'], FILTER_VALIDATE_EMAIL))
            {
                return array(
                    'message' => localize('Entered e-mail address is invalid', 'register'),
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_INVALID_FORMAT',
                    'field' => 'mail'
                );
            }
        }



        // ===== passwords
        if (!$this->disabledFields['passwd'])
        {
            if ($this -> fieldsSettings['passwd']['generate'])
            {
                $this->source['passwd'] = generateRandomString(9);
                
            } else {
                $this->source['passwd'] = trim($this->source['passwd']);
    
                if (!$this->source['passwd'])
                {
                    return array(
                        'message' => localize('Please fill password field', 'register'), // Mark: localize
                        'code' => 'PASSWD_FILL',
                        'field' => 'passwd'
                    );
                }
    
                if (strlen($this->source['passwd']) > $this->fieldsSettings['passwd']['lengthTo'] or strlen($this->source['passwd']) <= $this->fieldsSettings['passwd']['lengthFrom'])
                {
                    return array(
                        'message' => localize('Invalid password length', 'register'), // Mark: localize
                        'settings' => $this->fieldsSettings['passwd'],
                        'code' => 'PASSWD_LENGTH',
                        'field' => 'passwd'
                    );
                }
    
                if ($this->source['passwd'] != $this->source['passwd_repeat'])
                {
                    return array(
                        'message' => localize('Passwords do not match', 'register'), // Mark: localize
                        'settings' => $this->fieldsSettings['passwd'],
                        'code' => 'PASSWD_MATCH_FIELDS',
                        'field' => 'passwd_repeat'
                    );
                }
            }
        }



        // check if login or password is already taken
        $uCheck = $this -> panthera -> db -> query('SELECT `login`, `mail` FROM `{$db_prefix}users` WHERE `login` = :login OR `mail` = :mail',
            array(
                'login' => $this->source['login'],
                'mail' => $this->source['mail']
            )
        );

        if ($uCheck -> rowCount())
        {
            $fetch = $uCheck->fetch(PDO::FETCH_ASSOC);

            if ($fetch['mail'] == $this->source['mail'])
            {
                return array(
                    'message' => localize('This e-mail address was already used to register another account', 'register'),
                    'code' => 'MAIL_DUPLICATED',
                    'field' => 'mail'
                );
            }

            if ($fetch['login'] == $this->source['login'])
            {
                return array(
                    'message' => localize('This login is already taken', 'register'),
                    'code' => 'LOGIN_DUPLICATED',
                    'field' => 'login'
                );
            }
        }

        // additional fields
        $additionalFields = $this->validateAdditionalFields();

        if (!$additionalFields or is_array($additionalFields))
        {
            return $additionalFields;
        }

        return True;
    }

    /**
     * Create new user
     *
     * @return bool
     */

    public function execute()
    {
        $this -> panthera -> logging -> output('Creating new user', 'register');

        createNewUser(
            $this->source['login'],
            $this->source['passwd'],
            $this->source['fullname'],
            $this->panthera->config->getKey('register.group', 'users', 'string', 'register'),
            '',
            $this->panthera->locale->getActive(),
            $this->source['mail'],
            '',
            $this->panthera->config->getKey('register.avatar', '{$PANTHERA_URL}/images/default_avatar.png', 'string', 'register'),
            $_SERVER['REMOTE_ADDR'],
            (bool)$this -> panthera -> config -> getKey('register.confirmation.required', 1, 'bool', 'register')
        );

        $u = new pantheraUser('login', $this->source['login']);

        // facebook integration
        if ($this -> panthera -> session -> exists('registerFacebook'))
        {
            $u -> acl -> set('facebook', $this -> panthera -> session -> get('registerFacebook'));
            $u -> acl -> save();
        }
        
        $this -> panthera -> get_options_ref('register.created.userObject', $u);
        
        $u -> save();

        return $u->exists();
    }

    /*
     * Check if form is enabled, here can be a simple configuration check placed
     *
     * @return bool
     */

    public function formEnabled()
    {
        return (bool)$this -> panthera -> config -> getKey('register.open', 0, 'bool', 'register');
    }

    public function displayForm()
    {
        if ($this -> panthera -> config -> getKey('register.facebook', 1, 'bool', 'register'))
        {
            $facebookDetails = '';

            $this -> panthera -> importModule('facebook');

            try {
                // a little bit cache
                if (!$this -> panthera -> session -> exists('facebookUserInfo'))
                {
                    $facebook = new facebookWrapper;
                    $facebookDetails = $facebook->api('/me');

                    if ($facebookDetails)
                        $this -> panthera -> session -> set('facebookUserInfo', $facebookDetails);

                } else {
                    $facebookDetails = $this -> panthera -> session -> get('facebookUserInfo');
                }

            } catch (Exception $e) {
                $this -> panthera -> logging -> output('Cannot connect to Facebook, exception: ' .$e->getMessage(), 'register');
            }

            if (isset($_GET['facebook']))
            {
                if ($facebookDetails and $_GET['facebook'] != 'remove')
                {
                    $this->source['login'] = $facebookDetails['username'];
                    $this->source['fullname'] = $facebookDetails['name'];
                    $this -> panthera -> session -> set('registerFacebook', $facebookDetails['id']);
                }

                if ($_GET['facebook'] == 'remove')
                {
                    $this -> panthera -> session -> remove('registerFacebook');
                }
            }

            $this -> panthera -> template -> push('facebookConnected', False);

            if ($this -> panthera -> session -> exists('registerFacebook'))
            {
                $this -> panthera -> template -> push('facebookConnected', True);
            }

            $this -> panthera -> template -> push('facebookEnabled', True);
            $this -> panthera -> template -> push('facebookDetails', $facebookDetails);
        }

        return parent::displayForm();
    }
}