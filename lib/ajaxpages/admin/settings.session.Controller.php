<?php
/**
  * Session configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Session configuration page controller
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
class settings_sessionAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool',
        'admin.settings.session',
    );
    
    protected $uiTitlebar = array(
        'Session, cookies and browser security settings', 'session'
    );
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> config -> getKey('cookie_encrypt', 1, 'bool');
        $this -> panthera -> locale -> loadDomain('session');
        $this -> panthera -> locale -> loadDomain('installer');
        
        // some defaults
        $this -> panthera -> config -> getKey('cookie_encrypt', 0, 'bool');
        $this -> panthera -> config -> getKey('session_lifetime', (86400*30), 'int');
        $this -> panthera -> config -> getKey('session_useragent', 1, 'bool');
        $this -> panthera -> config -> getKey('gzip_compression', 0, 'bool');
        $this -> panthera -> config -> getKey('header_maskphp', 1, 'bool');
        $this -> panthera -> config -> getKey('header_framing', 'allowall', 'string');
        $this -> panthera -> config -> getKey('header_xssprot', 0, 'bool');
        $this -> panthera -> config -> getKey('header_nosniff', 0, 'bool');
        $this -> panthera -> config -> getKey('hashing_algorithm', 'sha512', 'string');

        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings;
        $config -> add('session_useragent', localize('Strict browser check', 'session'), new integerRange(0, 1));
        $config -> setFieldType('session_useragent', 'bool');
        $config -> add('session_lifetime', localize('Session life time', 'installer'), new integerRange(0, 999999));
        
        $config -> add('cookie_encrypt', localize('Encrypt cookies', 'installer'), new integerRange(0, 1));
        $config -> setFieldType('cookie_encrypt', 'bool');
        
        $config -> add('gzip_compression', localize('GZip compression', 'session'), new integerRange(0, 1));
        $config -> setFieldType('gzip_compression', 'bool');
        
        $config -> add('header_maskphp', localize('Mask PHP version', 'installer'), new integerRange(0, 1));
        $config -> setFieldType('header_maskphp', 'bool');
        
        $config -> add('header_framing', localize('X-Frame', 'installer'), array(
            'sameorigin' => localize('Only on same domain', 'installer'), 
            'allowall' => localize('Yes', 'installer'),
            'deny' => localize('No', 'installer')
        ));
        
        $config -> add('header_xssprot', localize('IE XSS-Protection', 'installer'), new integerRange(0, 1));
        $config -> setFieldType('header_xssprot', 'bool');
        
        $config -> add('header_nosniff', localize('No-sniff header', 'installer'), new integerRange(0, 1));
        $config -> setFieldType('header_nosniff', 'bool');
        
        $config -> add('hashing_algorithm', localize('Password hashing method', 'installer'), array(
            'blowfish' => 'blowfish - ' .localize('Slower, but provides maximum security', 'installer'),
            'md5' => 'md5 - ' .localize('Faster, but very weak', 'installer'), 
            'sha512' => 'sha512 - ' .localize('Fast, and provides medium security level', 'installer')
        ));
        
        $config -> setDescription('header_xssprot', localize('Tell\'s Internet Explorer to turn on XSS-Protection mechanism', 'installer'));
        $config -> setDescription('session_useragent', localize('Useragent strict check', 'installer'));
        $config -> setDescription('cookie_encrypt', localize('Cookies can be encrypted with strong algorithm, so the user wont be able to read contents', 'installer'));
        $config -> setDescription('session_lifetime', localize('Maximum time user can be idle (in seconds)', 'installer'));
        $config -> setDescription('header_framing', localize('Allow your website to be framed using iframe tag', 'installer'));
        $config -> setDescription('header_maskphp', localize('Force HTTP server to show false informations about PHP version', 'installer'));
        $config -> setDescription('hashing_algorithm', localize('Strong hashing algorithms are great in cases when site\'s database leaks in to the web, the hackers would have a problem with reading a strongly hashed and salted password', 'installer'));
        $config -> setDescription('header_nosniff', localize('This can reduce some drive-by-download attacks', 'installer'));
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
        
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));
        
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}