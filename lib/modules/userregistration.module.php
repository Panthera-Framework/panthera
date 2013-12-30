<?php
/**
  * User registration module
  *
  * @package Panthera\modules\userregistration
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

class userRegistration extends validableForm
{
    public $disabledFields = array(); // eg. array('login')
    public $fieldsSettings = array(
        'login' => array('lengthFrom' => 5, 'lengthTo' => 16),
        'mail' => array('lengthFrom' => 5, 'lengthTo' => 48),
        'passwd' => array('lengthFrom' => 5, 'lengthTo' => 64),
        'fullname' => array('lengthFrom' => 2, 'lengthTo' => 64, 'optional' => True)
    );
    
    // templates
    public $formTemplateEnabled = 'registrationForm.tpl';
    public $formTemplateDisabled = 'registrationForm.closed.tpl';
    public $formName = '';
    
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
                    'message' => 'Please fill login field', 
                    'code' => 'LOGIN_FILL',
                    'field' => 'login'
                );
            }
            
            if (strlen($this->source['login']) > $this->fieldsSettings['login']['lengthTo'] or strlen($this->source['login']) <= $this->fieldsSettings['login']['lengthFrom'])
            {
                return array(
                    'message' => 'Invalid login field length',
                    'settings' => $this->fieldsSettings['login'],
                    'code' => 'LOGIN_LENGTH',
                    'field' => 'login'
                );
            }
            
            $regexp = $this -> panthera -> get_filters('createNewUser.loginRegexp', '/^[a-zA-Z0-9\-\.\,\+\!]+_?[a-zA-Z0-9\-\.\,\+\!]+$/D');
            
            if (!preg_match($regexp, $this->source['login']))
            {
                return array(
                    'message' => 'Invalid characters in login, allowed only A-Z, a-z, 0-9, -, +, !, and comma',
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
                    'message' => 'Please enter your full name', 
                    'code' => 'FULLNAME_FILL',
                    'field' => 'fullname'
                );
            }
            
            if ($this->source['fullname'])
            {
                if (strlen($this->source['fullname']) > $this->fieldsSettings['fullname']['lengthTo'] or strlen($this->source['fullname']) <= $this->fieldsSettings['fullname']['lengthFrom'])
                {
                    return array(
                        'message' => 'Invalid length',
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
                    'message' => 'Please fill mail field correctly', 
                    'code' => 'MAIL_FILL',
                    'field' => 'mail'
                );
            }
            
            if (strlen($this->source['mail']) > $this->fieldsSettings['mail']['lengthTo'] or strlen($this->source['mail']) <= $this->fieldsSettings['mail']['lengthFrom'])
            {
                return array(
                    'message' => 'Invalid login field length',
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_LENGTH',
                    'field' => 'mail'
                );
            }
            
            if ($this->source['mail'] != $this->source['mail_repeat'])
            {
                return array(
                    'message' => 'Entered e-mail address does not match confirmation e-mail adddress',
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_MATCH_FIELDS',
                    'field' => 'mail_repeat'
                );
            }
            
            if (!filter_var($this->source['mail'], FILTER_VALIDATE_EMAIL))
            {
                return array(
                    'message' => 'Entered e-mail address is invalid',
                    'settings' => $this->fieldsSettings['mail'],
                    'code' => 'MAIL_INVALID_FORMAT',
                    'field' => 'mail'
                );
            }
        }


        
        // ===== passwords
        if (!$this->disabledFields['passwd'])
        {
            $this->source['passwd'] = trim($this->source['passwd']);
            
            if (!$this->source['passwd'])
            {
                return array(
                    'message' => 'Please fill password field', // Mark: localize
                    'code' => 'PASSWD_FILL',
                    'field' => 'passwd'
                );
            }
            
            if (strlen($this->source['passwd']) > $this->fieldsSettings['passwd']['lengthTo'] or strlen($this->source['passwd']) <= $this->fieldsSettings['passwd']['lengthFrom'])
            {
                return array(
                    'message' => 'Invalid password length', // Mark: localize
                    'settings' => $this->fieldsSettings['passwd'],
                    'code' => 'PASSWD_LENGTH',
                    'field' => 'passwd'
                );
            }
            
            if ($this->source['passwd'] != $this->source['passwd_repeat'])
            {
                return array(
                    'message' => 'Passwords do not match', // Mark: localize
                    'settings' => $this->fieldsSettings['passwd'],
                    'code' => 'PASSWD_MATCH_FIELDS',
                    'field' => 'passwd_repeat'
                );
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
                    'message' => 'This e-mail address was already used to register another account',
                    'code' => 'MAIL_DUPLICATED',
                    'field' => 'mail'
                );
            }
            
            if ($fetch['login'] == $this->source['login'])
            {
                return array(
                    'message' => 'This login is already taken',
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

    public function execute()
    {
        return createNewUser(
            $this->source['login'],
            $this->source['passwd'],
            $this->source['fullname'],
            $this->panthera->config->getKey('register.group', 'users', 'string', 'register'),
            '',
            $this->panthera->locale->getActive(),
            $this->source['mail'],
            '',
            $this->panthera->config->getKey('register.avatar', '{$PANTHERA_URL}/images/default_avatar.png', 'string', 'register'),
            $_SERVER['REMOTE_ADDR']
        );
    }

    /*
     * Check if form is enabled, here can be a simple configuration check placed
     * 
     * @return bool
     */
    
    public function formEnabled()
    {
        return (bool)$this -> panthera -> config -> getKey('register.open', 0, 'bool');
    }
}