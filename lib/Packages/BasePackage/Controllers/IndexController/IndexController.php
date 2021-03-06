<?php
namespace Panthera\Packages\BasePackage\Controllers;

use Panthera\Components\Controller\BaseFrameworkController;
use Panthera\Components\Controller\Response;
use Panthera\Components\Controller\ResponseText;

/**
 * Panthera Framework 2
 * --------------------
 * Class IndexController
 *
 * @package Panthera\Packages\BasePackage\Controllers
 */
class IndexController extends BaseFrameworkController
{
    /**
     * @API
     * @return ResponseText
     */
    public function defaultAction()
    {
        return new ResponseText('Replace this controller with your own');
    }
}