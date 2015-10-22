<?php
use Panthera\core\controllers\BaseFrameworkController;
use Panthera\core\controllers\Response;
require __DIR__. '/.content/app.php';

class IndexController extends BaseFrameworkController
{
    /**
     * @API
     * @return Response
     */
	public function defaultAction()
	{
        $response = new Response('BaseView/index.tpl', [
            'testText' => 'This is a test of a controller that supports @API calls - type ?__returnType=json or ?__returnType=yaml',
        ]);

        $response->assign([
           'user' => new \Panthera\model\user(null),
        ]);

		return $response;
	}
}

$test = new IndexController();
$test->display();