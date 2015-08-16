<?php
/**
 * Panthera Framework 2 cache test cases
 *
 * @package Panthera\cache\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class CacheTest extends PantheraFrameworkTestCase
{
    /**
     * Check setting/getting values to cache
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testSetGetValue()
    {
        $this->setup();
        $this->app->cache->set('test', 'testValue', 5);
        $this->assertEquals('testValue', $this->app->cache->get('test'));
        $this->app->cache->delete('test');
    }

    /**
     * Check caching arrays
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testCachingArrays()
    {
        $this->setup();
        $testVariable = array(array('testValue1'), 'testValue2');
        $this->app->cache->set('test', $testVariable);
        $this->assertSame($testVariable, $this->app->cache->get('test'));
        $this->app->cache->delete('test');
    }

    /**
     * Test return of exists() method - true
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testExistsVariableTrue()
    {
        $this->setup();
        $this->app->cache->set('testExisting', true, 5);
        $this->assertTrue($this->app->cache->exists('testExisting'));
        $this->app->cache->delete('testExisting');
    }

    /**
     * Test return of exists() method - false
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testExistsVariableFalse()
    {
        $this->setup();
        $this->assertFalse($this->app->cache->exists('NonExistingVariable'));
    }

    /**
     * Test deleting variables from cache
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testDeleteVariable()
    {
        $this->setup();
        $this->app->cache->set("variableToDelete", true, 5);
        $this->app->cache->delete("variableToDelete");
        $this->assertNull($this->app->cache->get("variableToDelete"));
    }

    /**
     * Check clearing cache
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testClearCache()
    {
        $this->setup();
        $this->app->cache->set('test', true, 5);
        $this->app->cache->clear(0);
        $this->assertNull($this->app->cache->get('test'));
    }
}