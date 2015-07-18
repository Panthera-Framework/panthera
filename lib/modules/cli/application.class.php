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
     * Collected opts from commandline
     *
     * @var string[]
     */
    public $opts = array();

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

        // list of application authors
        $text .= "\n\nAuthors:\n" . implode("\n", array_keys($authors));

        print($text. "\n");
        exit;
    }

    /**
     * Case when cli argument was not recognized eg. --test but it's not recognized by application
     *
     * @override
     * @author Damian Kęska <damian@pantheraframework.org>
     * @param string $argumentName
     */
    public function cliArgumentNotFound($argumentName)
    {
        print("Unsupported argument: " .$argumentName. "\n");
        exit;
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

        $args = $_SERVER['argv'];
        unset($args[0]);

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

                } else {
                    $argShortName = substr($arg, 1);

                    if (isset($this->argumentsShort[$argShortName]))
                    {
                        $this->cliArgumentsCallFunction($this->argumentsShort[$argShortName], $i, $args);
                    } else {
                        $this->cliArgumentNotFound($argShortName);
                    }
                }

                unset($args[$i]);
            }
        }

        $this->opts = $args;
        $this->parseOpts($args);

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