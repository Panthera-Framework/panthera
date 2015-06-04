<?php
namespace Panthera\cli;

/**
 * Commandline tools for Panthera Framework
 *
 * @package Panthera\cli
 * @author Damian Kęska
 * @license LGPLv3
 */
if (!defined('IN_PANTHERA'))
    exit;

require_once pantheraCore::getContentDir('share/CommandLine.php');

/**
 * Commandline tools for Panthera Framework
 *
 * @package Panthera\cli
 * @author Damian Kęska
 */
class pantheraCli
{
    /**
     * Handle json and base64 encoded arguments to emulate CGI application
     *
     * @author Damian Kęska
     * @return \Panthera\cli\pantheraCli
     */
    public function __construct()
    {
        $this->picker = new color();

        if (!defined('PANTHERA_NO_STDIN_READ'))
        {
            $stdin = fopen('php://stdin', 'r');
            $read = '';
            $array = null;

            $read = trim(fread($stdin, 80960));
            fclose($stdin);

            if ($read)
            {
                if (substr($read, 0, 9) == 'base64://')
                {
                    $array = json_decode(base64_decode(substr($read, 8, strlen($read))), true);

                } elseif (substr($read, 0, 7) == 'json://') {
                    $array = json_decode(substr($read, 7, strlen($read)));
                } elseif (substr($read, 0, 1) == '?') {
                    parse_str(substr($read, 1, strlen($read)), $_GET);
                }
            }

            // unpack variables
            if ($array)
            {
                foreach ($array as $varName => $value)
                    $$varName = $value;
            }
        }
    }

    /**
     * Read input line
     *
     * @return string
     * @author Damian Kęska
     */
    public function readline($sec, $def='')
    {
        return trim(shell_exec('bash -c ' .
            escapeshellarg('phprlto=' .
                escapeshellarg($def) . ';' .
                'read -t ' . ((int)$sec) . ' phprlto;' .
                'echo "$phprlto"')));
    }

    /**
     * Clear the screen
     *
     * @author Damian Kęska
     * @return void
     */
    public function clear()
    {
        system("clear");
    }

    /**
     * Print normal text at specified coordinates
     *
     * @param int $x
     * @param int $y
     * @param string $text
     * @author Damian Kęska
     * @return void
     */
    public function printAt($x, $y, $text='')
    {
        echo "\033[".$y.";".$x."H".$text;
    }

    /**
     * Print colorized text at current cursor position
     *
     * @param string $text
     * @param string $fg Text color
     * @param string $bg Background color (optional)
     * @return void
     * @author Damian Kęska
     */
    public function colorPrint($text, $fg, $bg=null)
    {
        echo $this->picker->getColoredString($text, $fg, $bg);
    }

    /**
     * Reset color in console
     *
     * @return void
     * @author Damian Kęska
     */
    public function resetColor()
    {
        echo "\e[00m";
    }

    /**
     * Print colorized message at specified coordinates
     *
     * @param int $x
     * @param int $y
     * @param string $text Input text
     * @param string $front Text color
     * @param string $back Background color
     * @return void
     * @author Damian Kęska
     */
    public function colorPrintAt($x, $y, $text, $front, $back)
    {
        $color = new color();
        self::printAt($x, $y, $color->getColoredString($text, $front, $back));
    }

    /**
     * Erase specified text from screen
     *
     * @param int $x
     * @param int $y
     * @param int $length
     * @param int height
     * @param string $bg
     * @return void
     * @author Damian Kęska
     */
    public function erase($x, $y, $length, $height, $bg)
    {
        $str = '';
        for($i=0; $i<$length; $i++){ $str .= ' '; }
        for($j=0; $j<$height; $j++) { writeStr($x, $y+$j, $str, $bg, $bg); }
    }
}

/**
 * Abstract application base class
 * Extend this class with your app eg. myApplication extends application
 *
 * @package Panthera\cli
 * @author Damian Kęska
 * @license LGPLv3
 */
abstract class application
{
    // main loop, input handling
    protected $loopType = 'readlineEmulated';
    protected $inputTimeout = 99999; // default input timeout
    protected $sleepTime = 3; // refresh application content every X seconds (default: 3) so, every 3 seconds $this->main() will be executed
    protected $inputFunctions = array('help' => array('function' => '__input_help', 'description' => 'shows this message'), 'exit' => array('function' => 'exit', 'description' => 'Exit application'));
    protected $_waitAfterHelp = True;


    // interface
    protected $curses = null;
    protected $toolkit = 'texttable'; // toolkit we are going to use (avaliable toolkits are: texttable, ncurses, newt
    protected $screen; // printing text, colors etc.
    protected $_markedForClear = False;

    /**
     * In constructor we are preparing the environment
     *
     * @author Damian Kęska
     * @return \Panthera\cli\application
     */
    public function __construct()
    {
        global $panthera;
        global $argv;

        $this->panthera = $panthera;

        /*if (function_exists('pcntl_signal'))
        {
            declare(ticks = 1);
            pcntl_signal(SIGTERM, 'pa_exit');
            pcntl_signal(SIGINT, 'pa_exit');
            @pcntl_signal(SIGKILL, 'pa_exit');
        }*/

        // window utils eg. colors, printing text, clearing the screen
        $this->screen = $this->panthera->cli;

        // dont mess logs when in cli mode
        $panthera -> logging -> tofile = False;

        $this -> argv = CommandLine::parseArgs($argv);

        // signal handler
        $panthera->add_option('cliSignal', array($this, 'signalHandler'));

        // automaticaly include selected toolkit
        if ($this->toolkit == 'texttable')
        {
            require_once(PANTHERA_DIR. '/share/texttable.class.php');

        } elseif ($this->toolkit == 'ncurses') {

            // check if ncurses are installed in system
            if (!function_exists('ncurses_init'))
                die('Cannot initialize ncurses interface, please install "ncurses" PECL extension');

            $this->curses = ncurses_init();
        } elseif ($this->toolkit == 'newt') {

            // check if newt is avaliable
            if (!function_exists('newt_init'))
                die('Cannot initialize newt interface, please install "newt" PECL extension');

            newt_init();
        }
    }

    /**
     * Simple signal handler
     *
     * @param int $signal
     * @author Damian Kęska
     * @return void
     */
    public function signalHandler($signal)
    {
        pa_exit();
    }

    /**
     * Register a CLI command
     *
     * @param string $command User input
     * @param string $function Method inside of class
     * @param string $description Description
     * @author Damian Kęska
     * @return bool
     */
    public function registerCommand($command, $function, $description)
    {
        $this->inputFunctions[$command] = array('function' => $function, 'description' => $description);
        return True;
    }

    /**
     * Application main function. Should be overrided and used to display content right after launch.
     *
     * @override
     * @author Damian Kęska
     * @return void
     */
    protected function main()
    {
        $this->clear();
        print("Put you'r app main content here.");
    }

    /**
     * Show help menu
     *
     * @author Damian Kęska
     * @return void
     */
    protected function __input_help($args = null)
    {
        foreach ($this->inputFunctions as $key => $value)
        {
            echo $key. " - ".$value['description']."\n";
        }

        if ($this->_waitAfterHelp == True)
            $this->wait();
    }

    public static function syntaxBy($statement)
    {
        $by = array();

        if (count($statement) > 0)
        {
            foreach ($statement as $key => $value)
            {
                $t = explode('=', $value);
                $by[$t[0]] = $t[1];
            }
        }

        return $by;
    }

    /**
     * Exit application
     *
     * @author Damian Kęska
     * @return void
     */
    protected function quit()
    {
        pa_exit();
    }

    /**
     * Clear screen
     *
     * @param bool $immediately
     * @author Damian Kęska
     * @return void
     */
    protected function clear($immediately=False)
    {
        if ($immediately == True)
            $this->screen->clear();
        else
            $this->_markedForClear = True;
    }
    /**
     * Show invalid input / invalid command message etc.
     *
     * @override
     * @param array $command
     * @author Damian Kęska
     * @return void
     */
    protected function invalidInput($command = null)
    {
        print("Invalid command specified, see help\n");
    }

    /**
     * Catch user typed input
     *
     * @param string $input
     * @author Damian Kęska
     * @return void
     */
    protected function catchUserInput($input)
    {
        if (!$input)
            return False;

        $command = explode(' ', trim($input));

        if (array_key_exists($command[0], $this->inputFunctions))
        {
            $f = $this->inputFunctions[$command[0]]['function'];

            if (method_exists($this, $f))
                $this->$f($command);
            else
                die("Critical error: cannot find method '" .$f. "' for command '" .$command[0]. "'\n");
        } else
            $this->invalidInput($command);
    }

    /**
     * Don't close window immediately, just wait for user input
     *
     * @author Damian Kęska
     * @return string
     */
    public function wait($time = null)
    {
        if (!is_int($time))
            $time = $this->sleepTime;

        return $this->screen->readline($time);
    }

    /**
     * Run application
     *
     * @return void
     * @author Damian Kęska
     */

    public function run()
    {
        if ($this->loopType == 'readline')
        {
            // using built-in readline function
            while (True)
            {
                if ($this->_markedForClear == True)
                {
                    $this->screen->clear(True);
                    $this->_markedForClear = False;
                }

                $this->main();
                $this->catchUserInput(readline());
            }


            // to capture user input we can use emulated readline
        } elseif ($this->loopType == 'readlineEmulated') {

            while (True)
            {
                if ($this->_markedForClear == True)
                {
                    $this->screen->clear(True);
                    $this->_markedForClear = False;
                }

                $this->main();
                $this->catchUserInput($this->screen->readline($this->sleepTime));
            }

        } else {
            // if we dont need to take input from user we can just use sleep
            while (True)
            {
                $this->main();
                sleep($this->sleepTime);
            }
        }
    }
}



/**
 * Color picker class
 *
 * @see http://code.adamsfriends.com/2012/01/26/php-cli-tutorial/
 * @package Panthera\cli
 */
class color
{
    private $foregroundColors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    );

    private $backgroundColors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );

    /**
     * Colorize a string
     *
     * @param string $string Input string
     * @param null|string $foregroundColor
     * @param null|string $backgroundColor
     * @return string Output string
     */
    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foregroundColors[$foregroundColor])) {
            $colored_string .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }
        // Check if given background color found
        if (isset($this->backgroundColors[$backgroundColor])) {
            $colored_string .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    /**
     * Get list of foreground colors
     *
     * @return String[]
     */
    public function getForegroundColors()
    {
        return array_keys($this->foregroundColors);
    }

    /**
     * Get list of background colors
     *
     * @return String[]
     */
    public function getBackgroundColors()
    {
        return array_keys($this->backgroundColors);
    }
}

/**
 * Handles a signal
 *
 * @param string $signal
 * @author Damian Kęska <damian@pantheraframework.org>
 * @return void
 */
function cliSignal($signal)
{
    $panthera::getInstance()->get_options('cliSignal', $signal);
    pa_exit();
}

if (function_exists('pcntl_signal'))
{
    declare(ticks = 1);
    pcntl_signal(SIGTERM, 'cliSignal');
    pcntl_signal(SIGHUP,  'cliSignal');
    pcntl_signal(SIGUSR1, 'cliSignal');
    pcntl_signal(SIGINT, 'cliSignal');
    pcntl_signal(SIG_IGN, 'cliSignal');
    pcntl_signal(SIGABRT, 'cliSignal');
    pcntl_signal(SIGQUIT, 'cliSignal');
}