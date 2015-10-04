<?php
namespace Panthera;

/**
 * Base Exceptions list
 *
 * @package Panthera\Exceptions
 * @author Damian Kęska <webnull.www@gmail.com>
 */

/**
 * Base PantheraFrameworkException
 *
 * @package Panthera
 * @author Damian Kęska <webnull.www@gmail.com>
 */
class PantheraFrameworkException extends \Exception
{
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }
}

/**
 * InvalidConfigurationException
 *
 * @package Panthera
 */
class InvalidConfigurationException extends PantheraFrameworkException {};

/**
 * FileNotFoundException
 *
 * @package Panthera
 */
class FileNotFoundException extends PantheraFrameworkException {};

/**
 * SyntaxException
 *
 * @package Panthera
 */
class SyntaxException extends PantheraFrameworkException {};

/**
 * FileException
 *
 * @package Panthera
 */
class FileException extends PantheraFrameworkException {};

/**
 * FileException
 *
 * @package Panthera
 */
class DatabaseException extends PantheraFrameworkException {};

/**
 * ValidationException
 *
 * @package Panthera
 */
class ValidationException extends PantheraFrameworkException
{
    public $column = null;
    public $class = null;

    public function __construct($message, $code, $class = null, $column = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->class = $class;
        $this->column = $column;
    }
};

/**
 * Class ControllerException
 *
 * @package Panthera\core\controllers
 */
class ControllerException extends PantheraFrameworkException {};