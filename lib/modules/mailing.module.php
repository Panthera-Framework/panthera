<?php
/**
  * Simple interface for creating mail messages
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
// include phpmailer liblary
require_once(PANTHERA_DIR. '/share/phpmailer/class.phpmailer.php');
require_once(PANTHERA_DIR. '/share/phpmailer/class.smtp.php');


class mailMessage
{
    public $mailer, $panthera;

    /**
	 * Pre-configuration of mailing system
	 *
	 * @param bool $debug
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($debug=False)
    {
        global $panthera;
        $this->panthera = $panthera;

        // initialize phpmailer
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
    
        $this->panthera->logging->output('mailMessage::Adding attachment ' .$file. ' as ' .$newName, 'mailing');
        
        return $this->mailer->AddAttachment($file, $newName);
    }

    /**
	 * Send mail
	 *
     * @param string $message Message
     * @param string $format Message format, default is "html" but can be also "plain"
	 * @return bool
	 * @author Damian Kęska
	 */

    public function send($message, $format='html')
    {
        if (strtolower($format) == 'html')
            $this->mailer->IsHTML(True);

        // set mail body
        $this->mailer->Body = $message;
        return $this->mailer->Send();
    }
}
