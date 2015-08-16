<?php

/**
 * Panthera Framework 2 configuration test cases
 *
 * @package Panthera\configuration\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class ConfigurationTest extends PantheraFrameworkTestCase
{
    /**
     * Check setting variables to configuration
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testSet()
    {
        $this->setup();
        $this->app->config->set('testKey', 'testValue');
        $this->assertSame('testValue', $this->app->config->data['testKey']);
    }

    /**
     * Check getting values from configuration
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGet()
    {
        $this->setup();
        $this->app->config->set('testKey', 'testValueDifferent');
        $this->assertSame('testValueDifferent', $this->app->config->get('testKey'));
    }
}