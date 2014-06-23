<?php
/**
 * All purpose tools
 * 
 * @package Panthera\core\modules\tools
 * @license LGPLv3
 * @author Damian Kęska
 */
 
/**
 * All purpose tools
 * 
 * @package Panthera\core\modules\tools
 * @author Damian Kęska
 */

class Tools
{
    /**
     * Get query string form GET/POST or other array, supports exceptions (some arguments can be skipped)
     *
     * @param array|string $array Array of elements, or a string value "GET" or "POST"
     * @param array|string $mix Elements to add (useful if using "GET" or "POST" in first but want to add something) eg. "aaa=test&bbb=ccc" or array('aaa' => 'test', 'bbb' => 'ccc')
     * @param array|string $except List of parameters to skip eg. "display,cat" or array('display', 'cat')
     * @package Panthera\core\modules\tools
     * @return string
     * @author Damian Kęska
     */
    
    public static function getQueryString($array=null, $mix=null, $except=null)
    {
        if ($array === null)
            $array = $_GET;
        elseif ($array == 'GET')
            $array = $_GET;
        elseif ($array == 'POST')
            $array = $_POST;
        elseif (is_string($array)) {
            parse_str($array, $array);
        }
    
        if ($mix != null) {
            if (is_string($mix)) {
                parse_str($mix, $mix);
            }
    
            if (is_array($mix)) {
                $array = array_merge($array, $mix);
            }
        }
    
        if ($except !== null)
        {
            if (!is_array($except))
                $except = explode(',', $except);
    
            foreach ($except as $exception)
                unset($array[trim($exception)]);
        }
    
        return http_build_query($array);
    }
    
    /**
     * Strip new lines
     *
     * @param string $string
     * @package Panthera\core\modules\tools
     * @return string
     * @author Damian Kęska
     */
    
    public static function stripNewLines($str)
    {
        return str_replace("\r", '\\r', str_replace("\n", '\\n', $str));
    }
    
    /**
     * Convert bool, false, null to string
     *
     * @param mixed $input Input
     * @package Panthera\core\modules\tools
     * @author Damian Kęska
     * @return string
     */
    
    public static function toString($input)
    {
        if ($input === null)
            $input = 'null';
        elseif ($input === false)
            $input = 'false';
        elseif ($input === true)
            $input = 'true';
    
        return (string)$input;
    }
    
    /**
     * Get first non-null value
     *
     * @param mixed $1
     * @param mixed $2
     * @param mixed $3
     * @param mixed $n
     * @package Panthera\core\modules\tools
     * @return mixed
     */
    
    public static function fallbackValue()
    {
        $args = func_get_args();
    
        foreach ($args as $arg)
        {
            if ($arg)
                return $arg;
        }
    }
    
    /**
     * This function will safely parse meta tags from array
     *
     * @package Panthera\core\system\kernel
     * @param array $tags Meta tags in an associative array
     * @return string
     * @author Damian Kęska
     */
    
    public static function parseMetaTags($tags)
    {
        if (count($tags) == 0 or !is_array($tags))
            return "";
    
        $code = '';
    
        foreach ($tags as $meta)
            $code .= static::filterMetaTag($meta). ',';
    
        return rtrim($code, ',');
    }
    
    /**
     * Filter meta tag, strip quotes
     *
     * @param string $tag Input tag string
     * @package Panthera\core\modules\tools
     * @return string
     * @author Damian Kęska
     */
    
    public static function filterMetaTag($tag)
    {
        $a = array('"', "'");
        return trim(strip_tags(str_replace($a, '', $tag)));
    }
    
    /**
     * Create SEO friendly name
     *
     * @package Panthera\core\modules\tools
     * @param string $string Article title, or file name, just a string to be converted
     * @return string
     * @author Alexander <http://forum.codecall.net/topic/59486-php-create-seo-friendly-url-titles-slugs/#axzz2JCfcCHFX>
     */
    
    public static function seoUrl($string) 
    {
        //Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
        $string = strtolower($string);
        //Strip any unwanted characters
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
}
