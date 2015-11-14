<?php
namespace Panthera\Packages\admin\dashboard;
use Panthera\Components\Controller\BaseFrameworkController;
use Panthera\Components\Controller\Response;

class DashboardController extends BaseFrameworkController
{
    /**
     * @API
     * @return Response
     */
    public function defaultAction()
    {
        var_dump($this->request->get('c'));

        return new Response([
            'test' => 'aaa',
        ]);
    }
}