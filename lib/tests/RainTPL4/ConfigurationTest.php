<?php
/**
 * Panthera Framework / RainTPL4 configuration test cases
 *
 * @package Panthera\template\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class RainConfigurationTest extends PantheraFrameworkTemplatingTestCase
{
    /**
     * Check assigning variables
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testConfiguration()
    {
        $this->app->template->setConfiguration(array('debug' => true));
        $this->assertTrue($this->app->template->rain->config['debug']);
    }
}