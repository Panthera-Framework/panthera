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
     * List of CLI arguments, shortened eg. -h would equal --help
     *
     * @var array
     */
    protected $argumentsShort = array(
        'h' => 'help',
    );

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
            pcntl_signal(SIGINT,  '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIG_IGN, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGABRT, '\\Panthera\\cli\\cliSignal');
            pcntl_signal(SIGQUIT, '\\Panthera\\cli\\cliSignal');
        }

        $this->readArguments();
    }

    /**
     * Standard help function
     *
     * @cli --help, -h
     * @param string $value Optional value
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function help_cliArgument($value)
    {

    }

    /**
     * Case when cli argument was not recognized eg. --test but it's not recognized by application
     *
     * @param string $argumentName
     */
    public function cliArgumentNotFound($argumentName)
    {
        print("Unsupported argument: " .$argumentName. "\n");
        exit;
    }

    public function cliArgumentsCallFunction($function, $i)
    {
        $function = $function. '_cliArgument';
        $value = '';

        if (method_exists($this, $function))
        {
            if (isset($_SERVER['argv'][$i + 1]) && substr($_SERVER['argv'][$i + 1], 0, 1) !== '-')
            {
                $value = $_SERVER['argv'][$i + 1];
                unset($_SERVER['argv'][$i + 1]);
            }

            $this->$function($value);

        } else {
            // raise a custom error
            $this->cliArgumentNotFound($function);
        }
    }

    public function readArguments()
    {
        // go through all expressions in shell command eg. "deploy.php unit-tests --arg1 value" would be: ['deploy.php', 'unit-tests', '--arg1', 'value']
        foreach ($_SERVER['argv'] as $i => $arg)
        {
            $value = '';

            /**
             * Long arguments name support
             *
             * Examples:
             *     --help
             *     --set value
             */
            if (substr($arg, 0, 2) === '--')
            {
                $this->cliArgumentsCallFunction(substr($arg, 2), $i);
            }
            elseif (substr($arg, 0, 1) === '-') {
                $argShortName = substr($arg, 1);

                if (isset($this->argumentsShort[$argShortName]))
                {
                    $this->cliArgumentsCallFunction($this->argumentsShort[$argShortName], $i);
                } else {
                    $this->cliArgumentNotFound($argShortName);
                }
            }
        }
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