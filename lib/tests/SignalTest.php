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
}