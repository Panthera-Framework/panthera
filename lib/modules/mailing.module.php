<?php
/**
 * Simple interface for creating mail messages
 *
 * @package Panthera\modules\mailing
 * @author Damian Kęska
 * @license GNU Lesser General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;
  
// include phpmailer liblary
require_once PANTHERA_DIR. '/share/phpmailer/class.phpmailer.php';
require_once PANTHERA_DIR. '/share/phpmailer/class.smtp.php';

/**
 * Simple interface for creating mail messages
 *
 * @package Panthera\modules\mailing
 * @author Damian Kęska
 */

class mailMessage
{
    public $mailer;
    public $panthera;

    /**
     * Pre-configuration of mailing system
     *
     * @param bool $debug
     * @return void
     * @author Damian Kęska
     */

    public function __construct($debug=False)
    {
        $panthera = pantheraCore::getInstance();
        $this->panthera = $panthera;

        // initialize phpmailer
        $panthera -> config -> loadSection('mailing');
        $this -> mailer = new PHPMailer();
        $this -> mailer ->IsSMTP();
        $this -> mailer -> CharSet = "UTF-8";
        
        if ($debug)
            $this -> mailer -> SMTPDebug = 2;

        // are we using SSL connection?
        if ($panthera -> config -> getKey('mailing_smtp_ssl', 'bool'))
        {
            $this->panthera->logging->output('mailMessage::Using SSL', 'mailing');
            $this->mailer->SMTPSecure = "ssl";
        }

        // specify server adress and port
        if ($panthera -> config -> getKey('mailing_server') and $panthera -> config -> getKey('mailing_server_port'))
        {
            $this->mailer->Host = $panthera -> config -> getKey('mailing_server'); // eg. smtp.gmail.com
            $this->mailer->Port = $panthera -> config -> getKey('mailing_server_port'); // eg. 465
            $this->panthera->logging->output('mailMessage::Setting host ' .$this->mailer->Host. ':' .$this->mailer->Port, 'mailing');
        }

        // are we using authentication?
        if ($panthera -> config -> getKey('mailing_user', 'bool') and $panthera -> config -> getKey('mailing_password', 'bool'))
        {
            $this->mailer->SMTPAuth = true;

            // authentication
            $this->mailer->Username = $panthera -> config -> getKey('mailing_user');
            $this->mailer->Password = $panthera -> config -> getKey('mailing_password'); 

            $this->panthera->logging->output('mailMessage::Setting user ' .$this->mailer->Username. ' with passwd(' .strlen($this->mailer->Password). ')', 'mailing');
        }

        // message author
        if ($panthera -> config -> getKey('mailing_from', 'email'))
        {
            $this->mailer->SetFrom($panthera -> config -> getKey('mailing_from', 'email'));
            $this->panthera->logging->output('mailMessage::Setting from:' .$panthera -> config -> getKey('mailing_from', 'email'), 'mailing');
        }

        // fall back to built-in php mail() function (for shared hostings)        
        if ($panthera->config->getKey('mailing_use_php', True, 'bool'))
        {
            $this->mailer->Mailer = 'mail';
            
            /*if ($panthera -> config -> getKey('mailing_server_port'))
                ini_set('smtp_port', $panthera -> config -> getKey('mailing_server_port'));
                
            if ($panthera -> config -> getKey('mailing_from', 'email'))
                ini_set('sendmail_from', $panthera -> config -> getKey('mailing_from', 'email'));
                
            if ($panthera -> config -> getKey('mailing_server'))
                ini_set('SMTP', $panthera -> config -> getKey('mailing_server'));*/
        }
    }
    
    /**
     * Set mail subject
     *
     * @param string $subject
     * @return bool
     * @author Damian Kęska
     */

    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
        return True;
    }

    /**
     * Set from e-mail adress
     *
     * @param string $from
     * @return bool
     * @author Damian Kęska
     */

    public function setFrom($from)
    {
        $this->mailer->SetFrom($from);
        return True;
    }

    /**
     * Add recipient
     *
     * @param string $address E-mail address
     * @param string $name Recipient name
     * @return bool
     * @author Damian Kęska
     */

    public function addRecipient($address, $name='')
    {
        if(!$this->panthera->types->validate($address, 'email'))
        {
            $this->panthera -> logging -> output('mailMessage::Incorrect mail adress specified "' .$address. '"', 'mailing');
            return False;
        }

        if ($name != '')
            $this->mailer->AddAddress($address, $name);
        else
            $this->mailer->AddAddress($address);

        $this->panthera->logging->output('mailMessage::Setting to:' .$address, 'mailing');

        return True;
    }

    /**
     * Add attachment
     *
     * @param string $file Path to local file
     * @return bool
     * @author Damian Kęska
     */

    public function addAttachment ($file, $newName='')
    {
        if ($newName == '')
            $newName = basename($file);
    
        $this->panthera->logging->output('Adding attachment ' .$file. ' as ' .$newName, 'mailing');
        
        return $this->mailer->AddAttachment($file, $newName);
    }

    /**
     * Send mail
     *
     * @param string $message Message
     * @param string $format Message format, default is "html" but can be also "plain"
     * @param string $altBody Plaintext body to show in non-html clients when using HTML format as default
     * @return bool
     * @author Damian Kęska
     */

    public function send($message, $format='html', $altBody='')
    {
        if (strtolower($format) == 'html')
            $this->mailer->IsHTML(True);

        // set mail body
        $this->mailer->Body = $message;
        
        if ($altBody)
            $this->mailer->AltBody = $altBody; 
        
        $this -> panthera -> logging -> output('Sent', 'mailing');
        
        return $this->mailer->Send();
    }
    
    /**
     * Send a message
     * 
     * @param string $dataOrTemplate Raw message or template name
     * @param bool $isTemplate Is $dataOrTemplate a template or just a mail content
     * @param string $from Sender
     * @param string|array $to Recipient or list of recipients eg. array(0 => array('example@exampl.org', 'Example name'))
     * @param array $variables List of variables to pass to template
     * @param string $subject (Optional) Mail subject, it's optional when mail is from template and has it's own subject in database
     * @param array $attachments (Optional) Attachments
     * @author Damian Kęska
     * @return bool
     */
    
    public static function sendMail($dataOrTemplate, $isTemplate, $from, $to, $variables='', $subject='', $attachments='', $language='')
    {
        $panthera = pantheraCore::getInstance();
        $mail = new mailMessage;
        $mail -> setFrom($from);
        
        if (is_array($to))
        {
            if (is_array($to[0]))
            {
                foreach ($to as $recipient)
                    $mail -> addRecipient($recipient[0], $recipient[1]);
            } else {
                $mail -> addRecipient($to[0], $to[1]);
            }
        } elseif (is_string($to))
            $mail -> addRecipient($to);

        if ($isTemplate)
        {
            $obj = new mailTemplate('template', $dataOrTemplate);
            
            if (!$obj -> exists())
                throw new Exception('Invalid template name "' .substr($dataOrTemplate, 0, 16). '"', 731);
            
            if (!$language)
                $language = $panthera -> locale -> getActive();
            
            if (!is_array($variables))
                $variables = array();
            
            $variables = array_merge($panthera -> template -> vars, $variables, array(
                'from' => $from,
                'to' => $to,
                'dataOrTemplate' => $dataOrTemplate,
                'subject' => $subject,
                'user' => $panthera -> user,
            ));
            
            // language fallback
            $templateLanguages = mailTemplate::getTemplateLanguages($dataOrTemplate);
            
            if (!in_array($language, $templateLanguages))
            {
                if ($obj -> fallback_language and in_array($obj -> fallback_language, $templateLanguages))
                    $language = $obj -> fallback_language;
            }
            
            $htmlBody = $panthera -> template -> compile($language. '/' .$dataOrTemplate. '.tpl', True, $variables, '_mails');
            
            try {
                $plainBody = $panthera -> template -> compile($language. '/' .$dataOrTemplate. '.txt.tpl', True, $variables, '_mails');
            } catch (Exception $e) {
                $panthera -> logging -> output('No plaintext mail template found (' .$dataOrTemplate. '.txt), stripping out HTML tags to create plain version', 'mailing');
                $plainBody = strip_tags($htmlBody);
            }
            
            if (!$subject and $obj -> default_subject)
            {
                $unserialized = @unserialize($obj -> default_subject);
                
                if (isset($unserialized[$language]))
                    $subject = $unserialized[$language];
                else
                    $subject = $panthera -> locale -> localizeFromArray(pantheraLocale::selectStringFromArray($unserialized));
            }
        } else {
            $htmlBody = $dataOrTemplate;
            $plainBody = strip_tags($htmlBody);
        }
        
        // add attachments
        if ($attachments)
        {
            foreach ($attachments as $file)
            {
                $f = null;
                
                if (isset($file[1]))
                    $f = $file[1];
                
                $mail -> addAttachment($file[0], $f);
            }
        }
        
        // replace variables in topic (global variables)
        if ($panthera -> user and $panthera -> user -> exists())
        {
            $subject = str_replace('{$loggedUserName}', $panthera -> user -> getName(), $subject);
            $subject = str_replace('{$loggedUserLogin}', $panthera -> user -> login, $subject);
        }
        
        $subject = str_replace('{$dateNow}', date($panthera -> dateFormat), $subject);
        $subject = pantheraUrl($subject);
        
        // replace all string and int type variables from $variables in $subject
        if ($variables)
        {
            foreach ($variables as $varName => $var)
            {
                if (is_string($var) or is_numeric($var))
                    $subject = str_replace('{$' .$varName. '}', $var, $subject);
            }
        }
        
        $mail -> setSubject($subject);
        return $mail -> send($htmlBody, 'html', $plainBody);
    }
}

/**
 * Mail template
 *
 * @package Panthera\modules\mailing
 * @author Damian Kęska
 */

class mailTemplate extends pantheraFetchDB
{
    protected $_tableName = 'mails';
    protected $_idColumn = 'template';
    protected $_constructBy = array(
        'template', 'array',
    );
    protected $_meta;
    protected $_unsetColumns = array();
    
    /**
     * Get list of translations of single mail template
     * 
     * @param string $templateName Template name
     * @author Damian Kęska
     * @return array
     */
    
    public static function getTemplateLanguages($templateName)
    {
        $files = array_merge(
            glob(SITE_DIR. '/content/templates/_mails/templates/*/' .$templateName. '{.txt,}.tpl', GLOB_BRACE),
            glob(PANTHERA_DIR. '/templates/_mails/templates/*/' .$templateName. '{.txt,}.tpl', GLOB_BRACE)
        );
        
        if (!$files)
            return False;

        $results = array();
        
        foreach ($files as &$file)
            $results[] = basename(pathinfo($file, PATHINFO_DIRNAME));
        
        return $results;
    }
    
    /**
     * Get list of templates
     * 
     * @return array|bool
     */
    
    public static function getTemplates()
    {
        $files = array_merge(
            glob(SITE_DIR. '/content/templates/_mails/templates/*/*{.txt,}.tpl', GLOB_BRACE),
            glob(PANTHERA_DIR. '/templates/_mails/templates/*/*{.txt,}.tpl', GLOB_BRACE)
        );
        
        if (!$files)
            return False;
        
        $results = array();
        
        foreach ($files as $file)
        {
            $file = str_replace(array(
                SITE_DIR, PANTHERA_DIR, '/content/', 'content/',
            ), '', $file);
            
            $file = realpath(getContentDir($file));
            
            $templateName = str_replace('.txt', '', pathinfo(basename($file), PATHINFO_FILENAME));
            $language = basename(pathinfo($file, PATHINFO_DIRNAME));
            
            if (!isset($results[$templateName]))
            {
                $results[$templateName] = array(
                    'languages' => array(),
                    'html' => false,
                    'plain' => false,
                    'files' => array(),
                );
            }
            
            if(strpos(basename($file), '.txt') !== False)
                $results[$templateName]['plain'] = True;
            else
                $results[$templateName]['html'] = True;
            
            if (!in_array($language, $results[$templateName]['languages']))
                $results[$templateName]['languages'][] = $language;
            
            if (!in_array($file, $results[$templateName]['files']))
                $results[$templateName]['files'][] = $file;
        }

        return $results;
    }
    
    /**
     * Constructor that creates default data in table in case it does not exists, but the template exists in files itself
     * 
     * @param string|array $by
     * @param string $value
     * @author Damian Kęska
     */
    
    public function __construct()
    {
        call_user_func_array(array($this, 'parent::__construct'), func_get_args());
        
        // adding a mail template to database if not added yet
        if (!$this -> exists())
        {
            $templateName = func_get_arg(1);
            
            if (glob(SITE_DIR. '/content/templates/_mails/templates/*/' .$templateName. '{.txt,}.tpl', GLOB_BRACE) or glob(PANTHERA_DIR. '/templates/_mails/templates/*/' .$templateName. '{.txt,}.tpl', GLOB_BRACE))
            {
                $this -> panthera -> logging -> output('Adding "' .$templateName. '" to database', 'mailTemplate');
                
                $this -> panthera -> db -> insert('mails', array(
                    'template' => $templateName,
                    'enabled' => 0,
                    'default_subject' => '',
                    'fallback_language' => 'english',
                ));
                
                // construct object second time after adding new row to table
                call_user_func_array(array($this, 'parent::__construct'), func_get_args());
            }
        }
    }

    /**
     * Get template content
     * 
     * @param string $language Template language
     * @param string $format Format eg. HTML or plain
     * @author Damian Kęska
     * @return string|bool
     */

    public function getContent($language, $format='html')
    {
        $extension = '.txt.tpl';

        if (strtolower($format) == 'html' or !$format)
            $extension = '.tpl';
        
        $path = getContentDir('templates/_mails/templates/' .$language. '/' .$this->template.$extension);
        
        if (!$path)
            return false;
        
        return file_get_contents($path);
    }

    /**
     * Set template content
     * 
     * @param string $content Template content
     * @param string $language Language
     * @param string $format HTML or plain
     * @author Damian Kęska
     * @return bool
     */

    public function setContent($content, $language, $format='html')
    {
        $extension = '.txt.tpl';

        if (strtolower($format) == 'html' or !$format)
            $extension = '.tpl';
        
        $savePath = SITE_DIR. '/content/templates/_mails/templates/' .$language;
        
        // create mails directory
        if (!is_dir(SITE_DIR. '/content/templates/_mails/'))
            mkdir(SITE_DIR. '/content/templates/_mails/');
            
        if (!is_dir(SITE_DIR. '/content/templates/_mails/templates'))
            mkdir(SITE_DIR. '/content/templates/_mails/templates');
       
        if (!is_dir($savePath))
            mkdir($savePath);
        
        $fp = @fopen($savePath. '/' .$this -> template.$extension, 'w');
        @fwrite($fp, $content);
        @fclose($fp);
        
        return is_file($savePath. '/' .$this->template.$extension);
    }
    
    /**
     * Get topic in selected language
     * 
     * @param string $language Language
     * @author Damian Kęska
     * @return string|null
     */
    
    public function getTopic($language)
    {
        if (!$this -> panthera -> locale -> exists($language))
            return False;
        
        $array = @unserialize($this -> default_subject);
        
        if (isset($array[$language]))
            return $array[$language];
    }
    
    /**
     * Set topic for selected language
     * 
     * @param string $language Topic language
     * @param string $topic Topic itself
     * @author Damian Kęska
     * @return bool
     */
    
    public function setTopic($language, $topic)
    {
        if (!$this -> panthera -> locale -> exists($language))
            return False;
        
        $array = @unserialize($this -> default_subject);
        
        if (!$array)
            $array = array();
        
        $array[$language] = $topic;
        $this -> default_subject = serialize($array);
        $this -> save();
        
        return True;
    }
}