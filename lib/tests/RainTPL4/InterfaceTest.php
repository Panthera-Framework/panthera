<?php
/**
 * Panthera Framework interface test cases
 *
 * @package Panthera\template\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class InterfaceTest extends PantheraFrameworkTestCase
{
    /**
     * Check assigning variables
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testAssigningVariables()
    {
        $this->setup();
        $this->app->template->assign('testVariable', 'testValue');
        $this->assertEquals('testValue', $this->app->template->display('{$testVariable}', true, true));
    }

    /**
     * Check displaying arrays
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testAssigningArrays()
    {
        $this->setup();
        $this->app->template->assign('testVariable', array(
            1, 2, 3
        ));
        $this->assertEquals('1,2,3', $this->app->template->display('{$testVariable[0]},{$testVariable[1]},{$testVariable[2]}', true, true));
    }
}