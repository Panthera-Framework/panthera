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

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
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
