<?php
/**
 * Panthera Framework 2
 * --------------------
 * BasePackage - IndexController unit testing
 *
 * @package Panthera\Packages\BasePackage\Tests
 */
class IndexControllerTest extends PantheraFrameworkTestCase
{
    /**
     * @see \Panthera\Packages\BasePackage\Controllers\IndexController::defaultAction()
     */
    public function testResponse()
    {
        $controller = new \Panthera\Packages\BasePackage\Controllers\IndexController();
        $this->assertEquals('Replace this controller with your own', $controller->defaultAction()->getVariable('text'));
    }
}