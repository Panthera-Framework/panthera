<?php
/**
 * CommandLine class
 *
 * Command Line Interface (CLI) utility class.
 *
 * @author              Patrick Fisher <patrick@pwfisher.com>
 * @since               August 21, 2009
 * @see                 https://github.com/pwfisher/CommandLine.php
 */
class CommandLine
{
    public static $args;

    /**
     * PARSE ARGUMENTS
     * 
     * This command line option parser supports any combination of three types
     * of options (switches, flags and arguments) and returns a simple array.
     * 
     * [pfisher ~]$ php test.php --foo --bar=baz
     *  [long]
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     * 
     * [pfisher ~]$ php test.php -abc
     *  [short]
     *   ["a"]     => true
     *   ["b"]     => true
     *   ["c"]     => true
     * 
     * [pfisher ~]$ php test.php arg1 arg2 arg3
     *  [opts]
     *   [0]       => "arg1"
     *   [1]       => "arg2"
     *   [2]       => "arg3"
     * 
     * @author              Patrick Fisher <patrick@pwfisher.com>
     * @author              Damian Kęska <webnull.www@gmail.com>
     * @since               August 21, 2009
     * @see                 https://github.com/pwfisher/CommandLine.php
     * @see                 http://www.php.net/manual/en/features.commandline.php
     *                      #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
     *                      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
     * @usage               $args = CommandLine::parseArgs($_SERVER['argv']);
     */


    public static function parseArgs($argv = null)
    {
        if (is_string($argv))
            $argv = explode(' ', $argv);

        $argv = $argv ? $argv : $_SERVER['argv'];
        array_shift($argv);
        $out = array('long' => array(), 'short' => array(), 'opts' => array());


        // splitting up keys with escaped spaces eg. "Jan\ Kowalski" -> this will be incorrectly splitted in to two args and must be join into one
        $joined = "";
        $nArgs = array();

        foreach ($argv as $arg)
        {
            // join all items
            if (substr($arg, (strlen($arg)-1), strlen($arg)) == "\\")
            {
                $joined .= substr($arg, 0, (strlen($arg)-1)). " ";
            } else {

                // last item
                if ($joined != '')
                {
                    $joined .= $arg;
                    $nArgs[] = $joined;
                    $joined = "";

                } else {
                    // without space
                    $joined = "";
                    $nArgs[] = $arg;
                }
            }
        }

        foreach ($nArgs as $arg)
        {
            // --foo --bar=baz
            if (substr($arg, 0, 2) === '--')
            {
                $eqPos                  = strpos($arg, '=');

                // --foo
                if ($eqPos === false)
                {
                    $key                = substr($arg, 2);
                    $value              = isset($out[$key]) ? $out[$key] : true;
                }

                // --bar=baz
                else
                {
                    $key                = substr($arg, 2, $eqPos - 2);
                    $value              = substr($arg, $eqPos + 1);

                }

                $out['long'][$key] = trim(trim($value, '"'), "'");
            }

            // -k=value -abc
            else if (substr($arg, 0, 1) === '-')
            {
                // -k=value
                if (substr($arg, 2, 1) === '=')
                {
                    $key                = substr($arg, 1, 1);
                    $value              = substr($arg, 3);
                    $out['short'][$key]          = trim(trim($value, '"'), "'");
                }
                // -abc
                else
                {
                    $chars              = str_split(substr($arg, 1));
                    foreach ($chars as $char)
                    {
                        $key            = $char;
                        $value          = isset($out[$key]) ? $out[$key] : true;
                        $out['short'][$key]      = trim(trim($value, '"'), "'");
                    }
                }
            }

            // plain-arg
            else
            {
                $value                  = $arg;
                $out['opts'][]                  = $value;
            }
        }

        self::$args = $out;

        return $out;
    }

    /**
     * GET BOOLEAN
     */
    public static function getBoolean($key, $default = false)
    {
        if (!isset(self::$args[$key]))
        {
            return $default;
        }
        $value                          = self::$args[$key];

        if (is_bool($value))
        {
            return $value;
        }

        if (is_int($value))
        {
            return (bool)$value;
        }

        if (is_string($value))
        {
            $value                      = strtolower($value);
            $map = array(
                'y'                     => true,
                'n'                     => false,
                'yes'                   => true,
                'no'                    => false,
                'true'                  => true,
                'false'                 => false,
                '1'                     => true,
                '0'                     => false,
                'on'                    => true,
                'off'                   => false,
            );
            if (isset($map[$value]))
            {
                return $map[$value];
            }
        }

        return $default;
    }
}
