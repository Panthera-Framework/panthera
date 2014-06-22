<?php
/**
 * Additional array functions
 *
 * @package Panthera\core\modules\arrays
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */
 
/**
 * Additional array functions
 *
 * @package Panthera\core\modules\arrays
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class arrays
{
    /**
     * Sort multidimensional array by value inside of array
     *
     * @param array &$array Input array
     * @param string $key Key in array to sort by
     * @package Panthera\modules\arrays
     * @return null
     * @author Lohoris <http://stackoverflow.com/questions/2699086/sort-multidimensional-array-by-value-2>
     */
    
    public static function aasort (&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
    
        foreach ($array as $ii => $va)
            $sorter[$ii] = $va[$key];
    
        asort($sorter);
    
        foreach ($sorter as $ii => $va)
            $ret[$ii] = $array[$ii];
    
        $array = $ret;
    }
    
    /**
     * Reset array keys (example of input: 5 => 'first', 6 => 'second', example of output: 0 => 'first', 1 => 'second')
     *
     * @param array $array Input array
     * @package Panthera\modules\arrays
     * @return array
     * @author Damian Kęska
     */
    
    function array_reset_keys($array)
    {
        $newArray = array();
    
        foreach ($array as $value)
            $newArray[] = $value;
    
        return $newArray;
    }
    
    /**
     * Limit array by selected range eg. keys from range 40 to 50
     *
     * @param array $array
     * @param int $offset
     * @param int $limit
     * @package Panthera\modules\arrays
     * @return array
     * @author Damian Kęska
     */
    
    public static function limitArray($array, $offset=0, $limit=0)
    {
        $newArray = array();
    
        if ($offset == 0 and $limit == 0)
            return $array;
    
        $c = count($array);
        $i = 0;
    
        foreach ($array as $key => $value)
        {
            $i++;
    
            // rewrite only elements matching our range
            if ($i >= $limit and $i <= ($limit+$offset))
                $newArray[$key] = $value;
        }
    
        return $newArray;
    }
    
    
    /**
     * Flatten array of all depth into single depth array divided by separator (like in filesystem)
     * 
     * @param array $arr Input array
     * @param array $base Base array from recursion
     * @param string $divider_char Divider character eg. / by default
     * @author Rob Peck <http://www.robpeck.com/2010/06/diffing-flattening-and-expanding-multidimensional-arrays-in-php>
     * @author Damian Kęska
     * @return array
     */
    
    public static function flatten($arr, $base = "", $divider_char = "/") 
    {
        $ret = array();
        
        if(is_array($arr))
        {
            foreach($arr as $k => $v) 
            {
                if(is_array($v)) 
                {
                    $tmp_array = static::flatten($v, $base.$k.$divider_char, $divider_char);
                    $ret = array_merge($ret, $tmp_array);
                } else
                    $ret[$base.$k] = $v;
            }
        }
        return $ret;
    }
    
    /**
     * Walk an array recursively counting deep level
     *
     * @param array $array Input array
     * @param callable $callback Callback function($key, $value, $depth, $additional)
     * @param mixed $additional Additional argument to be passed to every callback
     * @param int $depth Depth counter
     * @package Panthera\core\modules\arrays
     * @return mixed
     * @author Damian Kęska
     */
    
    public static function arrayWalkRecursive(&$array, $callback, $additional=null, $depth=1)
    {
        foreach ($array as $key => &$value)
        {
            if (is_array($value))
                continue;
    
            $additional = $callback($key, $value, $depth, $additional);
        }
    
        foreach ($array as $key => &$value)
        {
            if (!is_array($value))
                continue;
    
            $additional = static::arrayWalkRecursive($value, $callback, $additional, $depth++);
        }
    
        return $additional;
    }
}
