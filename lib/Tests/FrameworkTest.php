<?php
/**
 * Panthera Framework 2 framework test cases
 *
 * @package Panthera\framework\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class FrameworkTest extends PantheraFrameworkTestCase
{
    /**
     * Test getInstance() function
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGetInstance()
    {
        $this->assertSame($this->app, $this->app->getInstance());
    }

    /**
     * Check FileNotFoundException in getPath() function
     *
     * @throws \Panthera\Classes\BaseExceptions\FileNotFoundException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGetPathNotFound()
    {
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\FileNotFoundException');
        $this->app->getPath('notExistingPath');
    }

    /**
     * Test getPath() function with no applicationIndex specified
     *
     * @throws \Panthera\Classes\BaseExceptions\PantheraFrameworkException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testNoApplicationIndex()
    {
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\PantheraFrameworkException');
        $this->app->applicationIndex = null;
        $this->app->getPath('noApplicationIndexHere');
    }

    /**
     * Test specifying application index by loadApplicationIndex() function
     *
     * @throws \Panthera\Classes\BaseExceptions\PantheraFrameworkException
     * @author Mateusz Warzyński <lnxmen@gmail.com>
     */
    public function testLoadApplicationIndex()
    {
        $this->app->loadApplicationIndex();
        $this->assertNotNull($this->app->applicationIndex);
    }

    /**
     * Check shell application with invalid argument name
     *
     * @throws \Panthera\Classes\BaseExceptions\FileNotFoundException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testShellApplicationNotFound()
    {
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\FileNotFoundException');
        $this->app->runShellApplication('notExistingShellApplication');
    }

    /**
     * Test baseClass __sleep() function
     *      just for test coverage
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testBaseClass__sleep()
    {
        $this->assertNotNull($this->app->config->__sleep());
    }

    /**
     * Test baseClass __wakeup() function
     *      just for test coverage
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testBaseClass__wakeup()
    {
        $this->assertNull($this->app->config->__wakeup());
    }

    /**
     * @see \Panthera\framework::getName();
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testGetName()
    {
        $this->app->config->set('application/name', 'My-Application');

        $this->assertEquals('My-Application', $this->app->getName());
        $this->assertEquals('MyApplication', $this->app->getName(true));
    }
}