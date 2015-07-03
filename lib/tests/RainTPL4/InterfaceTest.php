<?php
/**
 * Panthera Framework interface test cases
 *
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class InterfaceTest extends PantheraFrameworkTestCase
{
    /**
     * Check displaying sites.
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function test()
    {
        $this->app->template->display('index.tpl');
    }
}