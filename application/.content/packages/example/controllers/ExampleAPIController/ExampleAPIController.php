<?php
namespace PFApplication\Packages\example;

use Panthera\Components\Controller\BaseFrameworkController;
use Panthera\Components\Controller\Response;

class ExampleAPIController extends BaseFrameworkController
{
    /**
     * @API
     * @return Response
     */
    public function defaultAction()
    {
        // todo: implement indexing templates from application and library
        // todo: implement checking templatePath in indexed array
        $response = new Response([
            'testText' => 'This is a test of a controller that supports @API calls - type ?__returnType=json or ?__returnType=yaml'
        ], $this->app->appPath.'/.content/templates/index.tpl');

        $response->assign([
            'user' => new \Panthera\model\user(null),
        ]);

        return $response;
    }
}