<?php
/**
 * Panthera Framework 2 logging test cases
 *
 * @package Panthera\logging\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class LoggingTest extends PantheraFrameworkTestCase
{
    /**
     * Check logging displaying messages
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testOutput()
    {
        $this->setup();
        $this->app->logging->dateFormat = '';
        $this->app->logging->format = '%message';
        $this->app->logging->enabled = true;
        $this->assertEquals('testMessage', $this->app->logging->output('testMessage'));
    }
}