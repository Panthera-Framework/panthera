<?php
/**
  * Additional array functions
  *
  * @package Panthera\modules\arrays
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
 * Sort multidimensional array by value inside of array
 *
 * @param array &$array Input array
 * @param string $key Key in array to sort by
 * @package Panthera\modules\arrays
 * @return void
 * @author Lohoris <http://stackoverflow.com/questions/2699086/sort-multidimensional-array-by-value-2>
 */

function aasort (&$array, $key)
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
    
function limitArray($array, $offset=0, $limit=0)
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
 * Walk an array recursively counting deep level
 *
 * @param array $array Input array
 * @param callable $callback Callback function($key, $value, $depth, $additional)
 * @param mixed $additional Additional argument to be passed to every callback
 * @param int $depth Depth counter
 * @package Panthera\modules\arrays
 * @return mixed
 * @author Damian Kęska
 */

function arrayWalkRecursive($array, $callback, $additional=null, $depth=1)
{
    foreach ($array as $key => $value)
    {
        if (is_array($value))
        {
            continue;
        }
        
        $additional = $callback($key, $value, $depth, $additional);
    }
    
    foreach ($array as $key => $value)
    {
        if (!is_array($value))
        {
            continue;
        }
        
        $additional = arrayWalkRecursive($value, $callback, $additional, $depth++);
    }
    
    return $additional;
}
