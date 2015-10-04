<?php
namespace Panthera\core\controllers;

use Panthera\BaseFrameworkClass;
use Panthera\ControllerException;

/**
 * Base Framework Controller
 *
 * @package Panthera\core\controllers
 */
class BaseFrameworkController extends BaseFrameworkClass
{
	/**
	 * Select action method to execute basing on request
	 *
	 * @input action
	 * @return mixed
	 * @throws ControllerException
	 */
	public function selectAction()
	{
		$action = isset($_GET['action']) ? $_GET['action'] : 'default';
		$actionMethodName = $action . 'Action';

		if (method_exists($this, $actionMethodName))
		{
			return call_user_func([$this, $actionMethodName]);
		}
		else
		{
			throw new ControllerException('Invalid action selected', 'CONTROLLER_NO_ACTION');
		}
	}
}