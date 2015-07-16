<?php
namespace Panthera\cli;
use Panthera;

/**
 * Panthera Framework 2 shell application skeleton
 *
 *
 * @author Damian Kęska <damian@pantheraframework.org>
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
     * @author Damian Kęska <damian@pantheraframework.org>
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
     * Display help
     *
     * @todo Optional and non-optional arguments support
     * @cli optional
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function help_cliArgument()
    {
        $text = "Usage: " .basename($_SERVER['argv'][0]) . " [OPTIONS] [--ARGUMENTS]\n\n";
        $reflection = new \ReflectionClass(get_called_class());
        $authors = array();

        foreach ($reflection->getMethods() as $method)
        {
            if (strpos($method->getName(), '_cliArgument') !== false)
            {
                $commandName = str_replace('_cliArgument', '', $method->getName());
                $text .= "\t--" .$commandName;

                foreach ($this->argumentsShort as $shortArg => $longArg)
                {
                    if ($longArg === $commandName)
                    {
                        $text .= ", -" .$shortArg;
                        break;
                    }
                }

                $comment = explode("\n", $method->getDocComment());
                $text .= "\t" . ltrim($comment[1], ' * ');

                foreach ($comment as $line)
                {
                    $line = ltrim($line, ' * ');

                    if (strpos($line, '@author') === 0)
                    {
                        $authors[substr($line, 7)] = substr($line, 7);
                    }
                }
            }
        }

        if (method_exists($this, 'cliArgumentsHelpText'))
        {
            $text .= $this->cliArgumentsHelpText($text);
        }

        // list of application authors
        $text .= "\n\nAuthors:\n" . implode("\n", array_keys($authors));

        print($text. "\n");
        exit;
    }

    /**
     * Case when cli argument was not recognized eg. --test but it's not recognized by application
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @param string $argumentName
     */
    public function cliArgumentNotFound($argumentName)
    {
        print("Unsupported argument: " .$argumentName. "\n");
        exit;
    }

    /**
     * Call a method for shell argument
     *
     * @param string $function Function name eg. "help" or "list"
     * @param int $i Index in $_SERVER['argv']
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function cliArgumentsCallFunction($function, $i)
    {
        $function = str_replace('-', '_', $function). '_cliArgument';
        $value = '';

        if (method_exists($this, $function))
        {
            if (isset($_SERVER['argv'][$i + 1]) && substr($_SERVER['argv'][$i + 1], 0, 1) !== '-')
            {
                $value = $_SERVER['argv'][$i + 1];
                unset($_SERVER['argv'][$i + 1]);
            }

            print($this->$function($value));

        } else {
            // raise a custom error
            $this->cliArgumentNotFound($function);
        }
    }

    /**
     * Case when no any arguments was specified to application eg. "$ deploy" (without arguments)
     * Default show help command output
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    public function cliArgumentsNoArgumentSpecified()
    {
        return $this->help_cliArgument();
    }

    /**
     * Parse shell arguments and execute proper commands eg. --help will execute $this->help_cliArgument()
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function readArguments()
    {
        if (count($_SERVER['argv']) === 1)
        {
            return $this->cliArgumentsNoArgumentSpecified();
        }

        // go through all expressions in shell command eg. "deploy.php unit-tests --arg1 value" would be: ['deploy.php', 'unit-tests', '--arg1', 'value']
        foreach ($_SERVER['argv'] as $i => $arg)
        {
            // don't parse our command path/basename
            if ($i === 0)
            {
                continue;
            }

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

        return null;
    }
}

/**
 * Handles a signal
 *
 * @param string $signal
 *
 * @package Panthera\cli
 * @author Damian Kęska <damian@pantheraframework.org>
 * @return void
 */
function cliSignal($signal)
{
    Panthera\framework::getInstance()->signals->execute('panthera.cli.application.system.signal', $signal);
}