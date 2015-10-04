<?php
namespace Panthera\core\controllers;

use Panthera\ControllerException;
use Panthera\framework;
use Panthera\Signals;
use Symfony\Component\Yaml\Yaml;

/**
 * Response handling class, every controller should return an object response
 *
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
	 * @param string $type json|yaml,
	 *
	 * @throws ControllerException
	 * @return null|string
	 */
	public function encode($type = null)
	{
		$customEncoder = Signals::getInstance()->execute('Response.encode', $this);

		if (!$customEncoder instanceof $this && $customEncoder !== null)
		{
			return $customEncoder;
		}

		switch ($type)
		{
			case 'json':
			{
				return json_encode($this->variables);
			}

			case 'yaml':
			{
				return Yaml::dump($this->variables);
			}

			default:
			{
				throw new ControllerException('Cannot encode response with an unknown encoder', 'RESPONSE_UNKNOWN_ENCODER');
			}
		}
	}

	/**
	 * Display a template like a regular form
	 *
	 * @return string
	 */
	public function display()
	{
		$templating = framework::getInstance()->template;
		return $templating->display($this->template);
	}
}