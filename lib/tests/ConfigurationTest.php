<?php
/**
 * Panthera Framework 2 configuration test cases
 *
 * @package Panthera\Modules\Configuration\Tests
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
     * Check removing key from configuration
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testRemove()
    {
        $this->setup();
        $this->app->config->set('testKey', 'testValue');
        $this->app->config->remove('testKey');
        $this->assertArrayNotHasKey('testKey', $this->app->config->data);
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

    /**
     * Test configuration of arrays
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testArrays()
    {
        $config = $this->app->config;

        // insert data
        $config->set('tv', []);
        $config->set('tv/SG-1', 'S10E05');

        $this->assertSame('S10E05', $config->get('tv/SG-1'));
        $this->assertSame(['SG-1' => 'S10E05'], $config->get('tv'));

        // update data
        $config->set('tv/SG-1', 'S10E06');
        $this->assertSame('S10E06', $config->get('tv/SG-1'));

        // add more data
        $config->set('tv/SG-A', 'S01E02');

        // check if the keys are still at its place
        $this->assertSame('S10E06', $config->get('tv/SG-1'));
        $this->assertSame('S01E02', $config->get('tv/SG-A'));

        // remove one of key
        $config->remove('tv/SG-1');
        $this->assertNull($config->get('tv/SG-1'));
        $this->assertSame('S01E02', $config->get('tv/SG-A'));
    }
}