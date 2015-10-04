<?php
use Panthera\core\controllers\BaseFrameworkController;
use Panthera\core\controllers\Response;
require __DIR__. '/.content/app.php';

class IndexController extends BaseFrameworkController
{
	public function defaultAction()
	{
		return new Response('BaseView/index.tpl', [
			'testText' => 'This is a test of a controller',
		]);
	}
}