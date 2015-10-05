<?php
namespace Panthera\core\controllers;

use Panthera\ControllerException;
use Panthera\framework;
use Panthera\Signals;
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
    public function __construct($template = null, $variables = [])
    {
        // @todo: Validation
        $this->template = $template;
        $this->variables = $variables;
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
        $customEncoder = Signals::getInstance()->execute('Response.encode', $this);

        // @todo implement ResponseEncoder
        if ($customEncoder instanceof ResponseEncoder)
        {
            return $customEncoder->encode($this->filterVariables($this->variables), $this->variables, $this);
        }

        switch ($type)
        {
            case 'JSON':
            {
                return json_encode($this->filterVariables($this->variables));
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
                (isset($variable->controllerPublic) && $variable->controllerPublic) ?: ($allowed = true);
                method_exists($variable, '__exposePublic') ?: ($variable = $variable->__exposePublic());
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
     * Display a template like a regular form
     *
     * @return string
     */
    public function display()
    {
        $template = framework::getInstance()->template;
        $template->assign($this->variables);

        return $template->display($this->template);
    }
}