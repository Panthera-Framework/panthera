<?php
namespace Panthera\Components\Validator;

/**
 * Panthera Framework 2
 * --------------------
 * List of basic validators like boolean, integer
 *
 * @package Panthera\Components\Validator
 */
class BasicValidators
{
    /**
     * Integers validator
     *
     * @param string $str
     * @param array $attr
     * @return bool
     */
    public static function integerValidator($str, $attr)
    {
        return is_numeric($str);
    }

    /**
     * Validate by regexp
     *
     * @param string $str
     * @param array $attr
     *
     * @return bool
     */
    public static function regexpValidator($str, $attr)
    {
        return (preg_replace('/' . $attr[1] . '/', '', $str) === $str) ? 1 : 'Value does not match range: ' . $attr[1];
    }

    /**
     * Boolean validator
     *
     * @param string $str
     * @param array $attr
     *
     * @return bool
     */
    public static function booleanValidator($str, $attr)
    {
        return ($str === true || $str == 'true' || intval($str) || $str === 't') ? 1 : 'Not a valid boolean';
    }

    /**
     * Validate e-mail address
     *
     * @param string $str
     * @param string $attr
     * @return bool
     */
    public static function emailValidator($str, $attr)
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }
}