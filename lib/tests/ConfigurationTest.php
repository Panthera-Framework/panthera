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

    /**
     * Check saving/reading array from configuration
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGetArray()
    {
        $this->setup();
        $simpleArray = array('test', array('test2' => 'value2'));
        $this->app->config->set('testArray', $simpleArray);
        $this->assertSame($simpleArray, $this->app->config->get('testArray'));
    }

    /**
     * Test loading configuration from database
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testDatabase()
    {
        $this->setup();
        $this->setupDatabase();
        $this->app->config->set('testDatabase', 'HelloDatabase!');
        $this->app->config->save();
        $data = $this->app->config->loadFromDatabase();
        $this->assertSame('HelloDatabase!', $data['testDatabase']);
    }
}