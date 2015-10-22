<?php
namespace Panthera\core\controllers;

use Panthera\BaseFrameworkClass;
use Panthera\ControllerException;
use Panthera\utils\ClassUtils;

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
     * @throws ControllerException
     * @return Response
     */
    public function selectAction()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'default';
        $actionMethodName = $action . 'Action';

        if (method_exists($this, $actionMethodName))
        {
            return [
                'response' => call_user_func([$this, $actionMethodName]),
                'action'   => $action,
                'method'   => $actionMethodName,
            ];
        }
        else
        {
            throw new ControllerException('Invalid action selected', 'CONTROLLER_NO_ACTION');
        }
    }

    /**
     * Select action and display output
     *
     * @throws ControllerException
     */
    public function display()
    {
        /** @var Response $response */
        $output = $this->selectAction();
        $response = $output['response'];

        if (!is_array($output) || !isset($output['response']) || !$output['response'] instanceof Response)
        {
            throw new \RuntimeException('Controller not returned a valid response');
        }

        if (ClassUtils::getTag(get_called_class(). '::' .$output['method']. '()', 'API') && $this->detectRequestType() != 'HTTP')
        {
            print($response->encode($this->detectRequestType()));
        }
        else
        {
            $response->display();
        }
    }

    /**
     * Detect what format to return as output basing on request
     *
     * eg. JSON, YAML, HTTP
     *
     * Input:
     *   a) As a header:
     *       ReturnType: json
     *
     *   b) As a GET/POST/PUT/etc parameter:
     *       __returnType=JSON
     */
    private function detectRequestType()
    {
        $type = 'HTTP';

        if (isset($_SERVER['HTTP_RETURNTYPE']) && $_SERVER['HTTP_RETURNTYPE'])
        {
            $type = $_SERVER['HTTP_RETURNTYPE'];
        }
        elseif (isset($_REQUEST['__returnType']) && $_REQUEST['__returnType'])
        {
            $type = $_REQUEST['__returnType'];
        }

        return strtoupper($type);
    }
}