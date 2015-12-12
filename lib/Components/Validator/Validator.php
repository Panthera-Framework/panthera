<?php
namespace Panthera\Components\Validator;
use Panthera\Classes\BaseExceptions\ValidationException;
use Panthera\Components\Kernel\Framework;

/**
 * Panthera Framework 2
 * --------------------
 * Validator Component, calls a correct validation function from a parsed validator string
 *
 * @package Panthera\Components\Validator
 */
class Validator
{
    /**
     * Example validator data strings:
     * - C/InternetProtocol::IP@local => \\Components\\InternetProtocol::IPValidator($data, ['local'])
     *
     * @param mixed $data
     * @param string $validator Validator data string
     *
     * @throws ValidationException
     * @return string|bool
     */
    public static function validate($data, $validator)
    {
        if ($validator[0] === '!')
        {
            if (!$data)
            {
                return 'No data entered';
            }

            $validator = substr($validator, 1);
        }

        // parse attributes block and path
        $attributes = explode('@', $validator);
        $parts = explode("/", $attributes[0]);
        unset($attributes[0]);

        // allow using shortcuts
        if (isset($parts[0]) && ($parts[0] === 'C' || $parts[0] === '[]'))
        {
            $parts[0] = 'Components';
        }

        // default namespace
        if (count($parts) === 1)
        {
            $parts = 'Components\\Validator\\BasicValidators::' . $parts[0];
        }
        else
        {
            $parts = implode('\\', $parts);
        }

        // split into a class name and function
        $exp = explode('::', $parts);
        $className = Framework::getInstance()->getClassName($exp[0]);
        $fName = $exp[1] . 'Validator';

        if (!method_exists($className, $fName))
        {
            throw new ValidationException('Cannot find "' . $className . '::' . $fName . '()" callback', 'NO_FUNCTION');
        }

        return $className::$fName($data, $attributes);
    }
}