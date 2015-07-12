<?php
namespace Panthera\cli;
use Panthera;

/**
 * Panthera Framework 2 shell application skeleton
 *
 *
 * @author Damian Kęska <damian.keska@fingo.pl>
 * @package Panthera\cli
 */
class application extends Panthera\baseClass
{
    /**
     * Constructor
     *
     * @author Damian Kęska <damian.keska@fingo.pl>
     */
    public function __construct()
    {
        parent::__construct();

        if (function_exists('pcntl_signal'))
        {
            declare(ticks = 1);
            pcntl_signal(SIGTERM, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGHUP,  '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGUSR1, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGINT, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIG_IGN, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGABRT, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGQUIT, '\\Panthera\\cli\\cliSignal');
        }

        $this->readArguments();
    }

    public function readArguments()
    {
        var_dump($_SERVER['argv']);
    }
}

/**
 * Handles a signal
 *
 * @param string $signal
 *
 * @package Panthera\cli
 * @author Damian Kęska <damian@pantheraframework.org>
 * @return null
 */
function cliSignal($signal)
{
    Panthera\framework::getInstance()->signals->execute('panthera.cli.application.system.signal', $signal);
}