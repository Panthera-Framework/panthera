<?php
namespace Panthera\Components\Controller;

use Panthera\Classes\BaseExceptions\ControllerException;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Signals\SignalsHandler;

use Symfony\Component\Yaml\Yaml;

/**
 * Response handling class, every controller should return an object response
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\core\controllers
 */
class Response
{
    /**
     * Template path / filename
     *
     * @var null
     */
    public $template = null;

    /**
     * Variables to pass to template or ajax response
     *
     * @var array
     */
    public $variables = [];

    /**
     * Constructor
     *
     * @param null $template
     * @param array $variables
     */
    public function __construct($variables = [], $template = null)
    {
        // @todo: Validation
        $this->template = $template;
        $this->variables = (array)$variables;
    }

    /**
     * Assign a variable to response
     *
     * @param array|string $variable Variable name or array of variables
     * @param mixed|null $value Value if specified a single variable or null if passed array in first argument
     * @return $this
     */
    public function assign($variable, $value = null)
    {
        if (!is_array($variable))
        {
            $variable = [
                $variable => $value,
            ];
        }

        $this->variables = array_merge($this->variables, $variable);
        return $this;
    }

    /**
     * @param string $variable
     * @return mixed
     */
    public function getVariable($variable)
    {
        return isset($this->variables[$variable]) ? $this->variables[$variable] : null;
    }

    /**
     * Encode a output response that will be sent to a browser
     *
     * @param string $type json|yaml
     *
     * @event Response.encode [$this]
     * @throws ControllerException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|string
     */
    public function encode($type = null)
    {
        $customEncoder = SignalsHandler::getInstance()->execute('Response.encode', $this);

        // @todo implement ResponseEncoder
        if ($customEncoder instanceof ResponseEncoderInterface)
        {
            return $customEncoder->encode($this->filterVariables($this->variables), $this->variables, $this);
        }

        switch ($type)
        {
            case 'JSON':
            {
                return json_encode($this->filterVariables($this->variables), JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR);
            }

            case 'YAML':
            {
                return Yaml::dump($this->filterVariables($this->variables));
            }

            default:
            {
                throw new ControllerException('Cannot encode response with an unknown encoder', 'RESPONSE_UNKNOWN_ENCODER');
            }
        }
    }

    /**
     * Filter all variables before putting them public
     *
     * Very important:
     *   a) Exposed CLASS OBJECT could implement a method "__exposePublic()" that return value could be visible instead of serialized object
     *   b) If CLASS OBJECT's property "controllerPublic" equals true then whole object could be exposed to public
     *
     * @keywords ACCESS CONTROL
     * @param array $variables
     * @return array
     */
    protected function filterVariables($variables)
    {
        $filtered = [];

        foreach ($variables as $key => $variable)
        {
            $allowed = false;

            if (is_object($variable))
            {
                if (isset($variable->controllerPublic))
                {
                    $allowed = true;
                }

                elseif (method_exists($variable, '__exposePublic'))
                {
                    $variable = $variable->__exposePublic();
                    $allowed = true;
                }
            }
            elseif (is_array($variable))
            {
                $allowed = true;
                $variable = $this->filterVariables($variable);
            }
            else
            {
                $allowed = true;
            }

            if ($allowed)
            {
                $filtered[$key] = $variable;
            }
        }

        return $filtered;
    }

    /**
     * Set HTTP response code
     *
     * @param int $code
     * @return bool
     */
    public function setCode($code = 200)
    {
        $codes = [
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '307' => 'Temporary Redirect',
            '400' => 'Bad Request',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '410' => 'Gone',
            '411' => 'Length Required',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',

            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
        ];

        if (isset($codes[$code]))
        {
            header('HTTP/1.1 ' . $code . ' ' . $codes[$code]);
            return true;
        }
    }

    /**
     * Display a template like a regular form
     *
     * @return string
     */
    public function display()
    {
        $template = Framework::getInstance()->template;
        $template->assign($this->variables);

        if (!$this->template)
        {
            return '';
        }

        // configure include paths
        $includePaths = Framework::getInstance()->packageManager->getIncludePaths();

        if ($includePaths)
        {
            $includePaths = array_map(function ($path) {
                if (is_dir($path . '/Templates/'))
                {
                    return $path . '/Templates/';
                }
            }, $includePaths);

            $template->setIncludePaths(array_filter($includePaths));
        }

        return $template->display($this->template);
    }
}