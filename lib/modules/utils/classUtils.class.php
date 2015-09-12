<?php
namespace Panthera\utils;

/**
 * Contains a lot of tools that could be used to eg. retrieve value of PHPDoc's tags positions, values
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\utils\classUtils
 */
class classUtils
{
    /**
     * Check if method has a specified tag in it's PHPDoc
     *
     * @param string $phpDoc PHPDoc input text
     * @param string $tagName Tag name eg. 'author'
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public static function methodHasTag($phpDoc, $tagName)
    {
        $phpDoc = self::getPHPDoc($phpDoc);

        $positions = self::getTagPositions($phpDoc, $tagName);
        return (is_array($positions) && count($positions) > 0);
    }

    /**
     * Get specified PHPDoc tag position(s)
     *
     * @param string $phpDoc PHPDoc input text
     * @param string $tagName Tag name eg. 'author'
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public static function getTagPositions($phpDoc, $tagName)
    {
        $phpDoc = self::getPHPDoc($phpDoc);

        $positions = [];
        $pos = 0;

        do
        {
            if (strlen($phpDoc) <= $pos)
            {
                break;
            }

            $pos = strpos($phpDoc, '* @' .$tagName, $pos + 1);

            if ($pos !== false)
            {
                $positions[] = [
                    $pos, ($pos + strlen($tagName) + 3), strpos($phpDoc, "\n", $pos),
                ];
            }

        } while ($pos !== false);

        return $positions;
    }

    /**
     * Get specified PHPDoc tag values
     *
     * @param string $phpDoc PHPDoc input text
     * @param string $tagName Tag name eg. 'author'
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public static function getTag($phpDoc, $tagName)
    {
        $phpDoc = self::getPHPDoc($phpDoc);

        $foundPositions = self::getTagPositions($phpDoc, $tagName);

        if (!$foundPositions)
        {
            return [];
        }

        $values = [];

        foreach ($foundPositions as $positions)
        {
            $value = ltrim(substr($phpDoc, $positions[1], ($positions[2] - $positions[1])));

            if ($value === '')
            {
                $value = true;
            }

            $values[] = $value;
        }

        return $values;
    }

    /**
     * Return PHPDoc comment
     *
     * @param callable $callable Array or string with naming for callable method
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public static function getPHPDoc($callable)
    {
        if (substr($callable, 0, 3) == '/**')
        {
            return $callable;
        }

        if (is_array($callable))
        {
            $class = $callable[0];
            $method = $callable[1];

            $reflection = new \ReflectionMethod($class, $method);
        }
        else
        {
            if (strpos($callable, '::') !== false && strpos($callable, "\n") === false)
            {
                list($class, $method) = explode('::', $callable);

                if (substr($method, -2) == '()')
                {
                    $reflection = new \ReflectionMethod($class, $method);
                }
                else
                {
                    $reflection = new \ReflectionClass($class);
                    $reflection = $reflection->getProperty($method);
                }
            }
        }

        if (!isset($reflection))
        {
            return '';
        }

        return $reflection->getDocComment();
    }
}