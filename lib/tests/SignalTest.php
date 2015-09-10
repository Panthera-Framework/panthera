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
     * Attach some callbacks with custom priority number, but attach them in different order what is important
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testSettingCustomSignalPriority()
    {
        $this->app->signals->attach('testSignal', function ($a) { return $a . '2'; /* test 1 */ }, 100);
        $this->app->signals->attach('testSignal', function ($a) { return $a . '1'; /* test 2, before test 1 */ }, 06);
        $this->app->signals->attach('testSignal', function ($a) { return $a . '3'; /* test 3, after all tests */ }, 120);

        $this->assertSame('0123', $this->app->signals->execute('testSignal', '0'));
    }

    /**
     * Test default priority assignment for registered signals
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testDefaultSignalPriority()
    {
        $this->app->signals->attach('testSignalWithDefaultPriority', function ($a) { return $a . '1'; });
        $this->app->signals->attach('testSignalWithDefaultPriority', function ($a) { return $a . '2'; });
        $this->app->signals->attach('testSignalWithDefaultPriority', function ($a) { return $a . '3'; });

        $this->assertSame('0123', $this->app->signals->execute('testSignalWithDefaultPriority', '0'));
    }

    /**
     * Test exception while setting not callable string/function as argument
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSettingNotCallableSignalFunction()
    {
        $this->setExpectedException("\\InvalidArgumentException");
        $this->app->signals->attach('notCallable', 'tryToCallMe');
    }

    /**
     * Test executing a slot where was no any method attached to
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testExecutingNotDefinedSignal()
    {
        $this->setup();
        $this->assertSame('signalMyFellow', $this->app->signals->execute('notExistingSignalName', 'signalMyFellow'));
    }
}