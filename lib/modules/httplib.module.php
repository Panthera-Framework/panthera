<?php
/**
  * Simple HTTP library for Panthera Framework
  *
  * @package Panthera\modules\httplib
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
class httplib
{
    public static $userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20130331 Firefox/21.0';

    public static function get($url, $method=null, $options=null)
    {
        // compatibility
        if (!is_array($options))
        {
            $options = array();
        }
    
        // initialize curl resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // default headers
        $headers = array(
            'Accept-Language:en-US,en;q=0.8,pl;q=0.6',
            'Cache-Control:max-age=0',
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'DNT:1'
        );

        // user defined headers
        if (isset($options['headers']))
        {
            if (is_array($options['headers']))
            {
                $headers = array_merge($headers, $options['headers']);
            }
        }

        // referer
        if (!isset($options['referer']))
        {
            $options['referer'] = parse_url($url, PHP_URL_SCHEME). '//' .parse_url($url, PHP_URL_HOST);
        }
        
        curl_setopt($curl, CURLOPT_REFERER, $options['referer']);
        
        // useragent
        $userAgent = self::$userAgent;
        
        if (isset($options['userAgent']))
        {
            $userAgent = $options['userAgent'];
        }
        
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        
        if ($method == 'POST')
        {
            curl_setopt ($curl, CURLOPT_POST, 1);
            //curl_setopt ($curl, CURLOPT_POSTFIELDS, $str);
        }
        
        $data = curl_exec($curl);
        curl_close($curl);
        
        return $data;
    }
}
