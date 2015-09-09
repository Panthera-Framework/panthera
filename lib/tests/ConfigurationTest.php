<?php

/**
 * Panthera Framework 2 configuration test cases
 *
 * @package Panthera\configuration\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class ConfigurationTest extends PantheraFrameworkTestCase
{
    public $integrationTestsForTestCase = false;

    /**
     * Check setting variables to configuration
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSet()
    {
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
        $this->app->config->set('testKey', 'testValueDifferent');
        $this->assertSame('testValueDifferent', $this->app->config->get('testKey'));
    }

    /**
     * Test loading configuration from database
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testDatabase()
    {
        // make sure the key is not on production database
        $testKey = 'testDatabase-' .microtime(true). '-' .rand(999, 9999);

        $this->app->config->set($testKey, 'HelloDatabase!');
        $this->app->config->save();
        $this->app->config->data = array();
        $this->app->config->loadFromDatabase();
        $this->assertSame('HelloDatabase!', $this->app->config->get($testKey));
    }
}