<?php
class reCaptcha_captchaExtension extends captcha
{
    protected $publicKey = '';
    protected $privateKey = '';
    protected $__apiURL = 'http://www.google.com/recaptcha/api';
    protected $__httplib = null;
    
    /**
     * Construct a reCaptcha object
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return null
     */
    
    public function __construct()
    {
        $this -> __httplib = new httplib;
        $this -> publicKey = panthera::getInstance() -> config -> getKey('captcha.re.publickey', '', 'string', 'captcha');
        $this -> privateKey = panthera::getInstance() -> config -> getKey('captcha.re.privatekey', '', 'string', 'captcha');
    }
    
    /**
     * Generate a captcha code
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return string
     */
    
    public function generateCode()
    {
        $errorParameter = '';
        
        if (panthera::getInstance() -> config -> getKey('captcha.re.userError', true, 'bool', 'captcha'))
            $errorParameter = '&error=incorrect-captcha-sol';
        
        return '<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' .$this -> publicKey.$errorParameter. '"></script>
          <noscript>
             <iframe src="http://www.google.com/recaptcha/api/noscript?k=' .$this -> publicKey.$errorParameter. '" height="300" width="500" frameborder="0"></iframe><br>
                 
             <textarea name="recaptcha_challenge_field" rows="3" cols="40">
             </textarea>
             
             <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
          </noscript>';
    }
    
    /**
     * Make a query using API
     * 
     * @param string $url URL eg. "/verify"
     * @param array $params POST parameters
     * @return array
     */
    
    public function queryAPI($url, $params)
    {
        return $this -> __httplib -> post($this -> __apiURL.$url, $params);
    }
    
    /**
     * Verify captcha string
     * 
     * @param array $args List of args eg. array('recaptcha_challenge_field' => 123, 'recaptcha_response_field' => 456)
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return bool|string Returns bool on success and string on failure (contains error title)
     */
    
    public function verify($args='')
    {
        if (isset($args['recaptcha_challenge_field']))
            $challenge = $args['recaptcha_challenge_field'];
        else
            $challenge = $_POST['recaptcha_challenge_field'];
            
        if (isset($args['recaptcha_response_field']))
            $response = $args['recaptcha_response_field'];
        else
            $response = $_POST['recaptcha_response_field'];
        
        $query = $this -> queryAPI('/verify', array(
            'privatekey' => $this -> privateKey,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
            'challenge' => $challenge,
            'response' => $response,
        ));
        
        $exp = explode("\n", $query);
        
        if ($exp[0] == "true")
            return true;
        
        panthera::getInstance() -> logging -> output('Got "' .$exp[1]. '" as response from reCaptcha server', 'captcha');
        return $exp[1];
    }
}
