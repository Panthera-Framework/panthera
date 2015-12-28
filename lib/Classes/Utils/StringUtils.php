<?php
namespace Panthera\Classes\Utils;

/**
 * Panthera Framework 2
 * --------------------
 * Basic utils for operation on strings
 *
 * @package Panthera\Classes\Utils
 */
class StringUtils
{
    /**
     * Get a string from a substring eg. "string1", "ddd123"
     *
     * @param string $string
     * @param int $offset
     * @param int $startPos
     * @param string $modifier
     *
     * @return string
     */
    public static function getString($string, &$offset = 0, &$startPos = 0, &$modifier = null)
    {
        $found = strpos($string, '"', $offset);

        if ($found === false)
        {
            return null;
        }

        $modifier = substr($string, $found - 1, $found);
        $startPos = $found;
        $ending = $found;

        while (true)
        {
            $ending = strpos($string, '"', $ending + 1);

            if ($ending === false || substr($string, $ending - 1, $ending) !== '/')
            {
                break;
            }
        }

        if ($ending === false)
        {
            return null;
        }

        $offset = $ending;

        return substr($string, $found + 1, ($ending - $found - 1));
    }

    /**
     * Get all strings from a string
     *
     * @param string $string
     * @return string[]
     */
    public static function getStrings($string)
    {
        $offset = 0;
        $strings = [];

        do
        {
            $found = static::getString($string, $offset);

            if ($found !== null)
            {
                $strings[] = $found;
            }

            $offset++;

        } while ($found !== null);

        return $strings;
    }
}