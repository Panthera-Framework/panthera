<?php
namespace Panthera\Components\CommandLine;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Panthera Framework 2 shell application skeleton
 *
 * @package Panthera\Components\CommandLine
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class Application extends BaseFrameworkClass
{
    /**
     * List of CLI arguments, shortened eg. -h would equal --help
     *
     * @var array
     */
    protected $argumentsShort = [
        'h' => 'help',
    ];

    /**
     * Collected opts from commandline
     *
     * @var string[]
     */
    public $opts = [];

    /**
     * List of collected arguments
     *
     * @var string[]
     */
    public $parsedArguments = [];

    /**
     * List of CLI arguments that was not recognized
     *
     * @var string[]
     */
    public $notFoundArguments = [];

    /**
     * @var bool
     */
    protected $allowUnknownArguments = false;

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
            pcntl_signal(SIGTERM, 'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIGHUP,  'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIGUSR1, 'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIGINT,  'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIG_IGN, 'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIGABRT, 'Panthera\\Components\\CommandLine\\cliSignal');
            pcntl_signal(SIGQUIT, 'Panthera\\Components\\CommandLine\\cliSignal');
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
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function help_cliArgument()
    {
        $text = "Usage: " .basename($_SERVER['argv'][0]) . " [OPTIONS] [--ARGUMENTS]\n";
        $reflection = new \ReflectionClass(get_called_class());
        $authors = array();

        foreach ($reflection->getMethods() as $method)
        {
            if (strpos($method->getName(), '_cliArgument') !== false)
            {
                $commandName = str_replace('_cliArgument', '', str_replace('__', '-', $method->getName()));
                $text .= "\n\t--" .$commandName;

                foreach ($this->argumentsShort as $shortArg => $longArg)
                {
                    if ($longArg === $commandName)
                    {
                        $text .= ", -" .$shortArg;
                        break;
                    }
                }

                $comment = explode("\n", $method->getDocComment());

                if (count($comment) > 1)
                {
                    $text .= "\t" . ltrim($comment[1], ' * ') . "\n";

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
        }

        $text .= $this->__helpText();

        // list of application authors
        $text .= "\n\nAuthors:\n" . implode("\n", array_keys($authors));

        print($text. "\n");
        exit;
    }

    /**
     * Dummy function to be extended
     *
     * @override
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function __helpText()
    {
        return '';
    }

    /**
     * Case when cli argument was not recognized eg. --test but it's not recognized by application
     *
     * @param string $argumentName CLI argument name
     * @param bool $exit Exit after displaying message
     *
     * @override
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    public function cliArgumentNotFound($argumentName, $exit = true)
    {
        print("Unsupported argument: " .str_replace('_cliArgument', '', $argumentName). "\n");
        if ($exit) exit;
    }

    /**
     * Check if in PHPDoc comment there is a line that begins with $attributeName and has text $oneOfValues eg. "@return" and "bool"
     *
     * @param string $doc PHPDoc comment text content
     * @param string $attributeName Attribute tag name
     * @param string $oneOfValues One of tag values
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function checkPHPDocAttribute($doc, $attributeName, $oneOfValues)
    {
        $splitted = explode("\n", $doc);

        foreach ($splitted as $line)
        {
            $line = ltrim(' * ', $line);

            if (strpos($line, $attributeName) === 0 && strpos($line, $oneOfValues) !== false)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Call a method for shell argument
     *
     * @param string $function Function name eg. "help" or "list"
     * @param int $i Index in $args
     * @param array $args
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function cliArgumentsCallFunction($function, $i, &$args)
    {
        // put into parsed arguments, so it could be used again
        $this->parsedArguments[$function] = isset($args[$i + 1]) ? $args[$i + 1] : true;

        $function = str_replace('-', '__', $function). '_cliArgument';
        $value = '';

        if (method_exists($this, $function))
        {
            $reflection = new \ReflectionMethod($this, $function);
            $comment = $reflection->getDocComment();

            if (isset($args[$i + 1]) && !$this->checkPHPDocAttribute($comment, '@cli', 'no-value') && substr($args[$i + 1], 0, 1) !== '-')
            {
                $value = $args[$i + 1];
                unset($args[$i + 1]);
            }

            print($this->$function($value));

        }
        else
        {
            // raise a custom error
            $this->notFoundArguments[] = $function;

            // find a possible value and remove from iteration, so in parseOpts() it will not be parsed
            if (isset($args[($i+1)]) && $args[($i+1)][0] !== '-')
            {
                unset($args[($i+1)]);
            }
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
     * @return null
     */
    public function readArguments()
    {
        $helpInvoked = false;

        if (count($_SERVER['argv']) === 1)
        {
            return $this->cliArgumentsNoArgumentSpecified();
        }

        $args = $_SERVER['argv'];
        unset($args[0]);

        if (in_array('--help', $args))
        {
            $helpInvoked = true;
            unset($args[array_search('--help', $args)]);

        }
        elseif (in_array('-h', $args))
        {
            $helpInvoked = true;
            unset($args[array_search('-h', $args)]);

        }
        else
        {
            // go through all expressions in shell command eg. "deploy.php unit-tests --arg1 value" would be: ['deploy.php', 'unit-tests', '--arg1', 'value']
            foreach ($args as $i => $arg)
            {
                /**
                 * Long arguments name support
                 *
                 * Examples:
                 *     --help
                 *     --set value
                 */
                if (substr($arg, 0, 1) === '-')
                {
                    if (substr($arg, 0, 2) === '--')
                    {
                        $this->cliArgumentsCallFunction(substr($arg, 2), $i, $args);
                    }
                    else
                    {
                        $argShortName = substr($arg, 1);

                        if (isset($this->argumentsShort[$argShortName]))
                        {
                            $this->cliArgumentsCallFunction($this->argumentsShort[$argShortName], $i, $args);
                        }
                        else
                        {
                            $this->notFoundArguments[] = $argShortName;

                            // find a possible value and remove from iteration, so in parseOpts() it will not be parsed
                            if (isset($args[($i+1)]) && $args[($i+1)][0] !== '-')
                            {
                                unset($args[($i+1)]);
                            }
                        }
                    }

                    unset($args[$i]);
                }
            }
        }

        $this->opts = $args;
        $this->parseOpts($args);

        if ($this->notFoundArguments && !$this->allowUnknownArguments)
        {
            foreach ($this->notFoundArguments as $argument)
            {
                $this->cliArgumentNotFound($argument, false);
            }

            exit;
        }

        if ($helpInvoked)
        {
            $this->help_cliArgument();
        }

        $this->executeOpts($args);
        return null;
    }

    /**
     * Dummy function for parsing opts
     *
     * @param string[] $args
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null;
     */
    public function parseOpts($args)
    {
        return null;
    }

    /**
     * Dummy function for executing post parsing actions
     *
     * @param string[] $args
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null;
     */
    public function executeOpts($args)
    {
        return null;
    }

    /**
     * Execute an external command
     *
     * @param string $command
     * @param string $function
     * @param bool $printCommand
     *
     * @return string
     */
    protected function exec($command, $function = 'passthru', $printCommand = true)
    {
        // replace standard variables
        $command = str_replace('%PF2_PATH%', PANTHERA_FRAMEWORK_PATH, $command);
        $command = str_replace('%APP_PATH%', $this->app->appPath, $command);

        if ($printCommand)
        {
            print("$ " .$command. "\n");
        }

        $result = 1;
        $output = '';

        switch ($function)
        {
            case 'passthru':
            {
                passthru($command, $result);
                break;
            }

            case 'exec':
            {
                $tmp = [];
                $output = exec($command, $tmp, $result);
                break;
            }

            case 'shell_exec':
            {
                $output = shell_exec($command);

                if ($output)
                {
                    $result = 0;
                }

                break;
            }
        }

        if ($result)
        {
            throw new \RuntimeException('Child process returned with code ' .$result);
        }

        return $output;
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
    framework::getInstance()->signals->execute('Panthera.Cli.Application.signal', $signal);
}