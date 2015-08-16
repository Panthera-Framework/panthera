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
        $this->setup();
        $this->assertSame($this->app, $this->app->getInstance());
    }

    /**
     * Check FileNotFoundException in getPath() function
     *
     * @throws \Panthera\FileNotFoundException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGetPathNotFound()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\FileNotFoundException');
        $this->app->getPath('notExistingPath');
    }

    /**
     * Test getPath() function with no applicationIndex specified
     *
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testNoApplicationIndex()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\PantheraFrameworkException');
        $this->app->applicationIndex = null;
        $this->app->getPath('noApplicationIndexHere');
    }

    /**
     * Test specifying application index by loadApplicationIndex() function
     *
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Mateusz Warzyński <lnxmen@gmail.com>
     */
    public function testLoadApplicationIndex()
    {
        $this->setup();
        $this->app->loadApplicationIndex();
        $this->assertNotNull($this->app->applicationIndex);
    }

    /**
     * Check shell application with invalid argument name
     *
     * @throws \Panthera\FileNotFoundException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testShellApplicationNotFound()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\FileNotFoundException');
        $this->app->runShellApplication('notExistingShellApplication');
    }
}