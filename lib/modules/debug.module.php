<?php
/**
 * Panthera debugging tools
 * 
 * @package Panthera\core\modules\debug
 * @author Damian Kęska
 */


/**
 * Static class for Panthera debugging tools
 * 
 * @package Panthera\core\modules\debug
 * @author Damian Kęska
 */

class debugTools
{
    /**
     * Print object informations
     *
     * @package Panthera\core\modules\debug
     * @param object $obj Input object
     * @param bool $returnAsString
     * @debug
     * @return void
     * @author Damian Kęska
     */
    
    public static function object_dump($obj, $returnAsString=False)
    {
        if (!is_object($obj))
            return False;
    
        $class = new ReflectionClass($obj);
    
        $data = array(
            'class' => get_class($obj),
            'file' => $class->getFileName(),
            'methods' => $class->getMethods(),
            'properties' => $class->getProperties(),
            'constants' => $class->getConstants(),
        );
    
        if ($returnAsString)
            return static::r_dump($data);
        else
            var_dump($data);
    }
    
    /**
     * Make a var_dump and return result
     *
     * @debug
     * @package Panthera\core\modules\debug
     * @return array
     * @author Damian Kęska
     */
    
    public static function r_dump()
    {
        ob_start();
        $var = func_get_args();
        call_user_func_array('var_dump', $var);
        return ob_get_clean();
    }
    
    /**
     * Prints print_r inside of HTML code replacing \n to <br> and spaces to &nbsp; HTML codes
     *
     * @debug
     * @package Panthera\core\modules\debug
     * @param mixed $input Input data of any type
     * @param bool $return Return output
     * @return string
     */
    
    public static function print_r_html($input, $return=false)
    {
        $result = nl2br(str_replace(' ', '&nbsp;', print_r($input, true)));
    
        if (!$return)
            print($result);
    
        return $result;
    }
    
    /**
     * List all class/object methods
     *
     * @debug
     * @package Panthera\core\modules\debug
     * @param object|string $obj
     * @param bool $return Return as string
     * @return string|bool
     * @author Damian Kęska
     */
    
    public static function object_info($obj, $return=False)
    {
        if (is_string($obj) and class_exists($obj))
            return ReflectionClass::export($obj, $return);
        elseif (is_object($obj))
            return ReflectionObject::export($obj, $return);
    
        return False;
    }
    
    /**
     * Checks if string is a valid json type
     *
     * @package Panthera\core\modules\debug
     * @return bool
     * @author Damian Kęska
     */
    
    public static function isJson($string) {
        @json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    /**
     * Is Panthera running in debugging mode?
     *
     * @package Panthera\core\modules\debug
     * @return bool
     * @author Damian Kęska
     */
    
    public static function isDebugging()
    {
        $panthera = pantheraCore::getInstance();
        return $panthera->logging->debug;
    }
    
    /**
     * Ajax equivalent of var_dump
     *
     * @package Panthera\core\system\kernel
     * @param mixed $mixed
     * @return null
     */
    
    public static function ajax_dump($mixed, $usePrint_r=False)
    {
        if (!$usePrint_r)
            $message = static::r_dump($mixed);
        else
            $message = print_r($mixed, true);
    
        ajax_exit(array(
            'status' => 'failed',
            'message' => $message,
        ));
    }
}
