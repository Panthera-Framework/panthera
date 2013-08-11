<?php
/**
    * @package Panthera
    * @subpackage core
    * @copyright (C) Damian Kęska, Mateusz Warzyński
    * @license GNU/AGPL, see lib/license.txt
    * Panthera is free software; you can redistribute it and/or
    * modify it under the terms of the GNU Affero General Public License 3
    * as published by the Free Software Foundation.
    *
    * Panthera is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU Affero General Public License
    * along with Panthera; if not, write to the Free Software
    * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
    * or see http://www.gnu.org/licenses/.
    */

if (!defined('IN_PANTHERA'))
    exit;

include(PANTHERA_DIR. '/share/CommandLine.php');

class pantheraCli
{
    public function __construct()
    {
        $this->picker = new cliColor();
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
     * @return void
     * @author Damian Kęska
     */

    public function clear() {
        system("clear");
    }

    /**
     * Print normal text at specified coordinates
     *
     * @param int $x
     * @param int $y
     * @param string $text
     * @return void
     * @author Damian Kęska
     */

    public function printAt($x, $y, $text='') {
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

    public function colorPrint($text, $fg, $bg=null) {
        echo $this->picker->getColoredString($text, $fg, $bg);
    }

    /**
     * Reset color in console
     *
     * @return void
     * @author Damian Kęska
     */

    public function resetColor() {
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

    public function colorPrintAt($x, $y, $text, $front, $back) {
        $color = new cliColor();
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

abstract class cliApp
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
     * @return void
     * @author Damian Kęska
     */

    public function __construct()
    {
        global $panthera;
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
      * @return void
      * @author Damian Kęska
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
     * @return bool
     * @author Damian Kęska
     */

    public function registerCommand($command, $function, $description)
    {
        $this->inputFunctions[$command] = array('function' => $function, 'description' => $description);
        return True;
    }

    /**
     * Application main function. Should be overrided and used to display content right after launch.
     *
     * @return void
     * @author Damian Kęska
     */

    protected function main()
    {
        $this->clear();
        print("Put you'r app main content here.");
    }

    /**
     * Show help menu
     *
     * @return void
     * @author Damian Kęska
     */

    protected function __input_help($args)
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
     * @return void
     * @author Damian Kęska
     */

    protected function quit()
    {
        pa_exit();
    }

    /**
     * Clear screen
     *
     * @param bool $immediately
     * @return void
     * @author Damian Kęska
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
     * @param array $command
     * @return void
     * @author Damian Kęska
     */

    protected function invalidInput($command)
    {
        print("Invalid command specified, see help\n");
    }

    /**
     * Catch user typed input
     *
     * @arg string $input
     * @return void
     * @author Damian Kęska
     */

    protected function catchUserInput ($input)
    {
        if ($input == "")
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
     * @return void
     * @author Damian Kęska
     */

    public function wait($time='')
    {
        if (!is_int($time))
            $time = $this->sleepTime;

        $this->screen->readline($time);
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



// http://code.adamsfriends.com/2012/01/26/php-cli-tutorial/
class cliColor {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
       // Set up shell colors
       $this->foreground_colors['black'] = '0;30';
       $this->foreground_colors['dark_gray'] = '1;30';
       $this->foreground_colors['blue'] = '0;34';
       $this->foreground_colors['light_blue'] = '1;34';
       $this->foreground_colors['green'] = '0;32';
       $this->foreground_colors['light_green'] = '1;32';
       $this->foreground_colors['cyan'] = '0;36';
       $this->foreground_colors['light_cyan'] = '1;36';
       $this->foreground_colors['red'] = '0;31';
       $this->foreground_colors['light_red'] = '1;31';
       $this->foreground_colors['purple'] = '0;35';
       $this->foreground_colors['light_purple'] = '1;35';
       $this->foreground_colors['brown'] = '0;33';
       $this->foreground_colors['yellow'] = '1;33';
       $this->foreground_colors['light_gray'] = '0;37';
       $this->foreground_colors['white'] = '1;37';

       $this->background_colors['black'] = '40';
       $this->background_colors['red'] = '41';
       $this->background_colors['green'] = '42';
       $this->background_colors['yellow'] = '43';
       $this->background_colors['blue'] = '44';
       $this->background_colors['magenta'] = '45';
       $this->background_colors['cyan'] = '46';
       $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

     // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

/**
  * Handles a signal
  *
  * @param string $signal
  * @return void
  * @author Damian Kęska
  */

function cliSignal($signal)
{
    global $panthera;
    $panthera -> get_options('cliSignal', $signal);
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
