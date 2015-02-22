<?php
/**
  * Contact module tools, frontpage data handler
  *
  * @package Panthera\modules\contact
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Frontpage contact data handler
  *
  * @package Panthera\modules\contact
  * @author Damian Kęska
  */

class contactFrontpage
{
    // some defaults
    public $fields = array(
        'p_contactMail' => array('required' => True, 'enabled' => True),
        'p_contactName' => array('required' => False, 'enabled' => True, 'maxlength' => 32, 'minlength' => 3, 'strictCheck' => False),
        'p_contactContent' => array('required' => True, 'enabled' => True, 'maxlength' => 8096, 'minlength' => 10, 'striphtml' => True),
        'p_contactJabber' => array('required' => False, 'enabled' => False),
        'p_contactTopic' => array('required' => True, 'enabled' => True, 'minlength' => 3, 'maxlength' => 128)
    );

    // TODO: Captcha support
    public $protection = array (
        'cookie' => array('time' => 3600, 'name' => 'p_contact')
    );

    // mailing defaults
    public $topicTemplate = '{$p_contactTopic}';
    public $from = 'example@example.org'; // this will be overwritted in __construct
    public $type = 'plain';
    public $mailBody = '{$p_contactContent}';

    public $settings = array(
        'text' => '<p>Paste contact data here</p>',
        'map' => '{"bounds":{"Z":{"b":50.52538601346569,"d":50.78657485494268},"fa":{"b":17.58736379609377,"d":18.27400930390627}},"zoom":10,"center":{"jb":50.65616198748283,"kb":17.93068655000002}}',
        'mail' => 'example@example.com'
    );

    protected $canSend = True;
    protected $captcha = null;

    /**
      * Constructor
      *
      * @param string $language
      * @return void
      * @author Damian Kęska
      */

    public function __construct($language='')
    {
        $panthera = pantheraCore::getInstance();

        if (!$language)
            $language = $panthera->locale->getActive();

        if ($panthera->config->getKey('contact.generic', False, 'bool', 'contact'))
        {
            $fieldName = 'contact.lang.all';
            $panthera -> template -> push ('p_contactLanguage', 'all');
        } else {
            $fieldName = 'contact.lang.' .$language;
            $panthera -> template -> push ('p_contactLanguage', $language);
        }

        $contactDefaults = array(
            'text' => '<p>Paste contact data here</p>',
            'map' => '{"bounds":{"Z":{"b":50.52538601346569,"d":50.78657485494268},"fa":{"b":17.58736379609377,"d":18.27400930390627}},"zoom":10,"center":{"jb":50.65616198748283,"kb":17.93068655000002}}',
            'mail' => 'example@example.com'
        );

        $this->settings = $panthera->config->getKey($fieldName, $contactDefaults, 'array', 'contact');
        $panthera -> addOption('template.display', array($this, 'applyToTemplate'));
        $this -> checkCanSend();
        
        $this -> captcha = captcha::createInstance();
        
        if ($this -> captcha && $panthera -> config -> getKey('contact.captcha', 1, 'bool', 'contact') && $this -> captcha -> enabled)
            $panthera -> template -> push('captchaCode', $this -> captcha -> generateCode());
    }

    /**
      * Check if current user can send an e-mail
      *
      * @hook contact.checkCanSend.canSend $canSend
      * @return void
      * @author Damian Kęska
      */

    public function checkCanSend()
    {
        $panthera = pantheraCore::getInstance();

        if ($this -> protection['cookie'])
        {
            if ($panthera -> session -> cookies -> get($this->protection['cookie']['name']))
                $this -> canSend = False;
        }

        $this->canSend = $panthera->executeFilters('contact.checkCanSend.canSend', $this->canSend);
    }

    /**
     * Set a cookie, save ip address, check captcha or any other method to secure form from spam
     *
     * @param string name
     * @return mixed
     * @author Damian Kęska
     */

    public function executeProtection()
    {
        $panthera = pantheraCore::getInstance();

        if ($this->protection['cookie'])
        {
            $panthera -> logging -> output ('Setting a new cookie "' .$this->protection['cookie']['name']. '"', 'contact');
            $panthera -> session -> cookies -> set($this->protection['cookie']['name'], 'yes', (time()+$this->protection['cookie']['time']));
        }

        $panthera -> execute('contact.executeProtection');
    }

    /**
     * Send a message
     *
     * @param array $array
     * @return bool
     * @author Damian Kęska
     */

    protected function sendMessage($array)
    {
        $panthera = pantheraCore::getInstance();

        if (!isset($array['p_contactMail']))
            $array['p_contactMail'] = $this->from;

        $this -> executeProtection();
        $panthera -> logging -> output ('Got input array=' .var_export($array, true), 'contact');

        $topic = str_ireplace('{$p_contactTopic}', @$array['p_contactTopic'], $this->topicTemplate);
        $topic = str_ireplace('{$p_contactName}', @$array['p_contactName'], $topic);
        $topic = str_ireplace('{$p_contactJabber}', @$array['p_contactJabber'], $topic);
        $topic = str_ireplace('{$p_contactMail}', @$array['p_contactMail'], $topic);

        $content = str_ireplace('{$p_contactContent}', @$array['p_contactContent'], $this->mailBody);
        $content = str_ireplace('{$p_contactName}', @$array['p_contactName'], $content);
        $content = str_ireplace('{$p_contactJabber}', @$array['p_contactJabber'], $content);
        $content = str_ireplace('{$p_contactMail}', @$array['p_contactMail'], $content);
        $content = str_ireplace('{$p_contactTopic}', @$array['p_contactTopic'], $content);

        $message = new mailMessage();
        $message -> setSubject ($topic);
        $message -> setFrom ($array['p_contactMail']);
        $message -> addRecipient($this->settings['mail']);

        return $message->send($content, $this->type);
    }

    /**
     * Validate all input data, eg. from $_POST source
     *
     * @hook contact.handledata $array, $valid
     * @param array $array
     * @return mixed
     * @author Damian Kęska
     */

    public function handleData($array)
    {
		$panthera = pantheraCore::getInstance();
        $panthera -> template -> push($array); // send back to template in case of any error
        
$this->canSend = true;
        if (!$this->canSend)
            return array('error' => localize('The mailing form is not avaliable at this time', 'contactpage'), 'messageid' => 'PROTECTION_CANNOT_SEND');

        // plugins support
        list($array, $valid) = $panthera -> executeFilters('contact.handledata', array($array, True));

        if ($valid !== True)
            return $valid;

        // contact e-mail address of user that is sending a form
        if ($this->fields['p_contactMail']['enabled'])
        {
            // if its a required field
            if (!$array['p_contactMail'] and $this->fields['p_contactMail']['required'])
                return array('error' => localize('Contact e-mail address is required', 'contactpage'), 'messageid' => 'REQUIRED_FIELD_EMAIL', 'field' => 'p_contactMail');

            if ($array['p_contactMail'])
            {
                if (!filter_var($array['p_contactMail'], FILTER_VALIDATE_EMAIL))
                    return array('error' => localize('Invalid e-mail address', 'contactpage'), 'messageid' => 'INVALID_FIELD_EMAIL', 'field' => 'p_contactMail');
            }
        }


        // name and surname
        if ($this -> fields['p_contactName']['enabled'])
        {
            // if its a required field
            if (!$array['p_contactName'] and $this->fields['p_contactName']['required'])
                return array('error' => localize('Please enter a valid name and surname', 'contactpage'), 'messageid' => 'REQUIRED_FIELD_NAME', 'field' => 'p_contactName');

            if ($array['p_contactName'])
            {
                // more strict check
                if ($this -> fields['p_contactName']['strictCheck'])
                {
                    $exp = explode(' ', $array['p_contactName']);

                    if (count($exp) != 2)
                        return array('error' => localize('Please enter a valid name and surname', 'contactpage'), 'messageid' => 'INVALID_FIELD_NAME', 'field' => 'p_contactName');
                }

                // max length
                if ($this -> fields['p_contactName']['maxlength'])
                {
                    if (strlen($array['p_contactName']) > $this->fields['p_contactName']['maxlength'])
                        return array('error' => localize('You\'r name and surname is too long', 'contactpage'), 'messageid' => 'TOO_LONG_FIELD_NAME', 'field' => 'p_contactName');
                }

                // min length
                if ($this -> fields['p_contactName']['minlength'])
                {
                    if (strlen($array['p_contactName']) < $this->fields['p_contactName']['minlength'])
                        return array('error' => localize('You\'r name and surname is too short', 'contactpage'), 'messageid' => 'TOO_SHORT_FIELD_NAME', 'field' => 'p_contactName');
                }

                // completly remove HTML tags
                if ($array['p_contactName'])
                    $array['p_contactName'] = strip_tags($array['p_contactName']);
            }
        }


        // content field
        if ($this -> fields['p_contactContent']['enabled'])
        {
            // if its a required field
            if (!$array['p_contactContent'] and $this->fields['field']['required'])
                return array('error' => localize('Please fill the content field', 'contactpage'), 'messageid' => 'REQUIRED_FIELD_CONTENT', 'field' => 'p_contactContent');

            if ($array['p_contactContent'])
            {
                // max length
                if ($this -> fields['p_contactContent']['maxlength'])
                {
                    if (strlen($array['p_contactContent']) > $this->fields['p_contactContent']['maxlength'])
                        return array('error' => localize('The content is too long', 'contactpage'), 'messageid' => 'TOO_LONG_FIELD_CONTENT', 'field' => 'p_contactContent');
                }

                // min length
                if ($this -> fields['p_contactContent']['minlength'])
                {
                    if (strlen($array['p_contactContent']) < $this->fields['p_contactContent']['minlength'])
                        return array('error' => localize('The content is too short', 'contactpage'), 'messageid' => 'TOO_SHORT_FIELD_CONTENT', 'field' => 'p_contactContent');
                }

                // quote all HTML tags
                if ($this -> fields['name']['striphtml'])
                    $array['p_contactContent'] = htmlspecialchars($array['p_contactContent']);
            }
        }


        // jabber field
        if ($this -> fields['p_contactJabber']['enabled'])
        {
            // if its a required field
            if (!$array['p_contactJabber'] and $this->fields['p_contactJabber']['required'])
                return array('error' => localize('Contact e-mail address is required', 'contactpage'), 'messageid' => 'REQUIRED_FIELD_JABBER', 'field' => 'p_contactJabber');

            if ($array['p_contactJabber'])
            {
                if (!filter_var($array['p_contactJabber'], FILTER_VALIDATE_EMAIL))
                    return array('error' => localize('Invalid Jabber address', 'contactpage'), 'messageid' => 'INVALID_FIELD_JABBER', 'field' => 'p_contactJabber');
            }
        }

        // topic field
        if ($this -> fields['p_contactTopic']['enabled'])
        {
            // if its a required field
            if (!$array['p_contactTopic'] and $this->fields['p_contactTopic']['required'])
                return array('error' => localize('Please enter a valid topic', 'contactpage'), 'messageid' => 'REQUIRED_FIELD_TOPIC', 'field' => 'p_contactTopic');

            if ($array['p_contactTopic'])
            {
                // max length
                if ($thisc-> fields['p_contactTopic']['maxlength'])
                {
                    if (strlen($array['p_contactTopic']) > $this->fields['p_contactTopic']['maxlength'])
                        return array('error' => localize('The topic is too long', 'contactpage'), 'messageid' => 'TOO_LONG_FIELD_TOPIC', 'field' => 'p_contactTopic');
                }

                // min length
                if ($this -> fields['p_contactTopic']['minlength'])
                {
                    if (strlen($array['p_contactTopic']) < $this->fields['p_contactTopic']['minlength'])
                        return array('error' => localize('The topic is too short', 'contactpage'), 'messageid' => 'TOO_SHORT_FIELD_TOPIC', 'field' => 'p_contactTopic');
                }

                // completly remove HTML tags
                $array['p_contactTopic'] = strip_tags($array['p_contactTopic']);
            }
        }

        if ($this -> captcha && $panthera -> config -> getKey('contact.captcha', 1, 'bool', 'contact') && $this -> captcha -> enabled and !is_bool($this -> captcha -> verify()))
            return array(
                'error' => localize('Invalid captcha code', 'contactpage'),
                'messageid' => 'INVALID_CAPTCHA_CODE',
                'field' => 'p_captchaCode',
            );

        return $this->sendMessage($array);
    }

    /**
      * Apply all data to template
      *
      * @return void
      * @author Damian Kęska
      */

    public function applyToTemplate()
    {
        $panthera = pantheraCore::getInstance();

        $panthera -> template -> push ('p_contactText', $this -> settings['text']);
        $panthera -> template -> push ('p_contactMap', $this -> settings['map']);
        $panthera -> template -> push ('p_contactMail', $this -> settings['mail']);
        $panthera -> template -> push ('p_contactFields', $this -> fields);
        
        if ($panthera -> config -> getKey('contact.captcha', 1, 'bool', 'contact') && $this -> captcha -> enabled)
            $panthera -> template -> push ('p_captchaCode', $this -> captcha -> generateCode());
    }
}