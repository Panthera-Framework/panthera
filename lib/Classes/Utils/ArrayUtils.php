<?php
namespace Panthera\Classes\Utils;

/**
 * Miscellaneous array functions
 *
 * @package Panthera\Classes\Utils
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class ArrayUtils
{
    /**
     * Get value from multidimensional array by path
     *
     * @param array $array Input array
     * @param string $path
     * @param null $default
     *
     * @throws \InvalidArgumentException
     * @link http://codeaid.net/php/set-value-of-an-array-using-xpath-notation
     * @return mixed
     */
    public static function getByXPath(array $array, $path, $default = null)
    {
        // specify the delimiter
        $delimiter = '/';

        // fail if the path is empty
        if (empty($path))
        {
            throw new \InvalidArgumentException('Path cannot be empty');
        }

        // remove all leading and trailing slashes
        $path = trim($path, $delimiter);

        // use current array as the initial value
        $value = $array;

        // extract parts of the path
        $parts = explode($delimiter, $path);

        // loop through each part and extract its value
        foreach ($parts as $part)
        {
            if (isset($value[$part]))
            {
                // replace current value with the child
                $value = $value[$part];
            }
            else
            {
                // key doesn't exist, fail
                return $default;
            }
        }

        return $value;
    }

    /**
     * Put a value at specified path in multidimensional array
     *
     * @param array $array Input array to work on
     * @param string $path Key position by xpath
     * @param mixed $value Value
     *
     * @throws \InvalidArgumentException
     * @link http://codeaid.net/php/set-value-of-an-array-using-xpath-notation
     * @return array Outputs modified array
     */
    public static function setByXPath(array $array, $path, $value)
    {
        // fail if the path is empty
        if (empty($path))
        {
            throw new \InvalidArgumentException('Path cannot be empty');
        }

        // fail if path is not a string
        if (!is_string($path))
        {
            throw new \InvalidArgumentException('Path must be a string');
        }

        // specify the delimiter
        $delimiter = '/';

        // remove all leading and trailing slashes
        $path = trim($path, $delimiter);

        // split the path in into separate parts
        $parts = explode($delimiter, $path);

        // initially point to the root of the array
        $pointer =& $array;

        // loop through each part and ensure that the cell is there
        foreach ($parts as $part)
        {
            // fail if the part is empty
            if (empty($part))
            {
                throw new \InvalidArgumentException('Invalid path specified: ' . $path);
            }

            // create the cell if it doesn't exist
            if (!isset($pointer[$part]) || !is_array($pointer[$part]))
            {
                $pointer[$part] = [];
            }

            // redirect the pointer to the new cell
            $pointer =& $pointer[$part];
        }

        // set value of the target cell
        $pointer = $value;

        return $array;
    }
}