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
    }
}