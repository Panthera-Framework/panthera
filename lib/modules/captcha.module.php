<?php
class captcha
{
    public $enabled = False;
    
    /**
     * Constructor
     * Prevents from constructing new captcha class object using "new" keyword
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     */
    
    public function __construct()
    {
        if (get_called_class() == 'captcha')
            throw new captchaException('Cannot create instance of captcha class, please use captcha::createInstance()', 3);
    }
    
    /**
     * Get captcha instance
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return mixed
     */
    
    public static function createInstance()
    {
        $panthera = panthera::getInstance();
        $captchaExtension = $panthera -> config -> getKey('captcha.extension', 'reCaptcha', 'string', 'captcha');
        
        if (!$captchaExtension)
            return false;
        
        // import captcha module from modules/captcha directory
        if (!$panthera -> moduleExists('captcha/' .$captchaExtension))
            throw new captchaException('Cannot find captcha module "captcha/' .$captchaExtension. '"', 1);
        
        $panthera -> importModule('captcha/' .$captchaExtension);
            
        // create new instance of captcha class and return
        $class = $captchaExtension. '_captchaExtension';
        if (!class_exists($class))
            throw new captchaException('Cannot find captcha class "' .$class. '"', 2);
        
        $object = new $class;
        
        if ($panthera -> config -> getKey('captcha.enabled', 1, 'bool', 'captcha'))
            $object -> enabled = true;
        
        return $object;
    }
}

class captchaException extends Exception {}
