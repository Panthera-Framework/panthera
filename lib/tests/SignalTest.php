<?php

/**
 * Panthera Framework 2 signals test cases
 *
 * @package Panthera\signals\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class SignalsTest extends PantheraFrameworkTestCase
{
    /**
     * Test attaching signals (\Panthera\logging -> clear() as clearLogging)
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testAttachSignal()
    {
        $this->setup();
        $this->app->signals->attach("clearLogging", array($this->app->logging, 'clear'));
        $this->assertArrayHasKey('clearLogging', $this->app->signals->registeredSignals);
    }

    /**
     * Test executing signals (clear output messages)
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testExecutingSignal()
    {
        $this->setup();
        $this->app->logging->enabled = true;
        $this->app->logging->output("Signals test.");

        $this->app->signals->attach("clearLogging", array($this->app->logging, 'clear'));
        $this->app->signals->execute("clearLogging");

        $this->assertSame(array(), $this->app->logging->messages);
    }

    /**
     * Test setting function's priority to signal.
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSettingSignalPriority()
    {
        $this->setup();
        $this->app->signals->attach("clearLogging", array($this->app->logging, 'clear'), 10);
        $this->assertNotNull($this->app->signals->registeredSignals["clearLogging"]['elements'][10]);
    }

    /**
     * Test exception while setting not callable string/function as argument
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSettingNotCallableSignalFunction()
    {
        $this->setup();
        $this->setExpectedException("\\InvalidArgumentException");
        $this->app->signals->attach('notCallable', 'tryToCallMe');
    }

    /**
     * Test executing not defined signal
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testExecutingNotDefinedSignal()
    {
        $this->setup();
        $this->assertSame('signalMyFellow', $this->app->signals->execute('notExistingSignalName', 'signalMyFellow'));
    }
}