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
        $array = static::toQueryArray($array);
    
        if ($mix != null)
        {
            if (is_string($mix))
                parse_str($mix, $mix);
            
    
            if (is_array($mix))
                $array = array_merge($array, $mix);
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
     * Build HTML input type hidden fields from array
     * This function can be helpful in creating dynamic GET forms
     * 
     * @param array|string $array Array of elements, or a string value "GET" or "POST"
     * @param array|string $mix Elements to add (useful if using "GET" or "POST" in first but want to add something) eg. "aaa=test&bbb=ccc" or array('aaa' => 'test', 'bbb' => 'ccc')
     * @param array|string $except List of parameters to skip eg. "display,cat" or array('display', 'cat')
     * @package Panthera\core\modules\tools
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return string
     */

    public static function buildHiddenFields($array, $mix=null, $except=null, $endl='')
    {
        $array = static::toQueryArray($array);
        
        if ($mix != null) {
            if (is_string($mix))
                parse_str($mix, $mix);
            
    
            if (is_array($mix))
                $array = array_merge($array, $mix);
        }
    
        if ($except !== null)
        {
            if (!is_array($except))
                $except = explode(',', $except);
    
            foreach ($except as $exception)
                unset($array[trim($exception)]);
        }
        
        $html = '';
        
        foreach ($array as $key => $value)
        {
            $html .= '<input type="hidden" name="' .$key. '" value="' .$value. '"' .$endl. '>'; 
        }
        
        return $html;
    }
    
    /**
     * Convert everything to query array. Eg. query string, "GET" or "POST" input
     * 
     * @param string|null|array $array Input
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return array
     */
    
    public static function toQueryArray($array)
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
        
        return $array;
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
    
    /**
     * Convert user-friendly date eg. +30 days to currentdate+30 days in $format
     * 
     * @param string|int $string Input date string or int
     * @param string $format (Optional) Date format, defaults to "d-m-Y H:i" (MySQL default)
     * @param string|int $now (Optional) Relative date
     * @author Damian Kęska
     * @return string
     */
    
    public static function userFriendlyStringToDate($string, $format='d-m-Y H:i', $now='now')
    {
        $string = trim($string); // strip out of whitespaces
        
        if (in_array(substr($string, 0, 1), array('+', '-')))
        {
            // integration with current active system locale
            $localized = array(
                localize('second') => 'second',
                localize('seconds') => 'seconds',
                localize('minute') => 'minute',
                localize('minutes') => 'minutes',
                localize('hour') => 'hour',
                localize('hours') => 'hours',
                localize('day') => 'day',
                localize('days') => 'days',
                localize('month') => 'month',
                localize('months') => 'months',
                localize('year') => 'year',
                localize('years') => 'years',
            );
            
            foreach ($localized as $translated => $original)
                $string = str_ireplace($translated, $original, $string);
            
            $date = new DateTime($now);
            $date -> modify($string);
            
            return $date -> format($format);
        }

        if (!is_int($string))
            $string = strtotime($string);

        return date($format, $string);
    }

    /**
     * Check if date expired
     * 
     * @param string|int $date Input date
     * @return bool
     */

    public static function dateExpired($date)
    {
        if (is_string($date))
            $date = strtotime($date);
        
        if ($date > time())
            return false;
        
        return true;
    }
    
    /**
     * Parse template variables in a string
     * 
     * Example:
     * <code>
     * $str = "Hello {$user.login}!";
     * 
     * var_dump(Tools::parseTemplateVars($str, 'user', $panthera -> user));
     *  => 'Hello admin!'
     * </code>
     * 
     * @param string $string Input string
     * @param string $objectName Object name
     * @param object $objectData|array pantheraFetchDb based object or associative array
     * @author Damian Kęska
     * @return string
     */
    
    public static function parseTemplateVars($string, $objectName, $objectData)
    {
        if (method_exists($objectData, 'getName'))
            $string = str_replace('{$' .$objectName. '.__name}', $objectData -> getName(), $string);
        
        if (!is_array($objectData))
            $objectData = $objectData -> getData();
        
        foreach ($objectData as $key => $value)
            $string = str_replace('{$' .$objectName. '.' .$key. '}', $value, $string);

        return $string;
    }
    
    /**
     * Get full request URL
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return string
     */
    
    public static function getRequestURL()
    {
        return "http://".strip_tags(addslashes($_SERVER['HTTP_HOST'])).$_SERVER['REQUEST_URI'];
    }
    
    /**
     * Parse array using a callback function
     * 
     * @param array $array Input array to parse values
     * @param string|callable $parser Parser function, defaults to intval
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return array
     */
    
    public static function parseArray($array, $parser='intval')
    {
        if (!is_callable($parser) or !function_exists($parser))
            return $array;
        
        if ($array)
        {
            foreach ($array as $key => &$value)
                $value = $parser($value);
        }
        
        return $array;
    }
    
    /**
     * Validate full name and surname
     * 
     * @param string $input Input full name
     * @author Damian Kęska
     * @return bool
     */
    
    public static function validateFullname($input)
    {
        $input = trim($input);
        $exp = explode(' ', $input);
        
        if (count($exp) < 2 || !static::beignsLowercase($exp[0]) || !static::beignsLowercase($exp[1]))
            return false;
        
        return true;
    }
    
    /**
     * Check if first character is lowercase
     * 
     * @param string $str Input string
     * @author Artefacto <http://stackoverflow.com/questions/2814880/how-to-check-if-letter-is-upper-or-lower-in-php>
     * @author Damian Kęska
     * @return bool
     */
    
    public static function beignsLowercase($str)
    {
        $chr = mb_substr($str, 0, 1, "UTF-8");
        return (mb_strtolower($chr, "UTF-8") != $chr);
    }
    
    /**
     * Return first non-null, non-zero, non-empty value from array
     * 
     * @param mixed ...
     * @return mixed
     */
    
    public static function selectFirstValue()
    {
        foreach (func_get_args() as $arg)
        {
            if ($arg)
                return $arg;
        }
    }
}
