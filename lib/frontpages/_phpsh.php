#!/usr/bin/env phpsh
<?php
/**
 * Front controller for CLI app - phpsh
 * 
 * Usage: fron shell type phpsh _phpsh.php, and your are in
 * 
 * To include your custom startup functions create "/content/phpsh.rc.php" file with your functions and classes
 * 
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @see http://www.phpsh.org/
 * @license LGPLv3
 */

# include app config and libs
define('PANTHERA_NO_STDIN_READ', true);
require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

if (PANTHERA_MODE == 'cgi')
    die('This controller is not available in CGI mode.');

/**
 * Front controller for CLI app - phpsh
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @see http://www.phpsh.org/
 */

class _phpshControllerSystem extends pageController
{
    /**
     * Set error reporting and printing to console
     *
     * @return null
     */

    protected function setReporting()
    {
        error_reporting(E_ALL);

        // print logging output directly to console
        $this -> panthera -> logging -> printOutput = True;
    }

    /**
     * Prints welcome text
     *
     * @return null
     */

    protected function printWelcomeText()
    {
        $out = "\n* Panthera Framework components:\n";
        $out .= "=> \$panthera -> user\n";
        $out .= "=> \$panthera -> session\n";
        $out .= "=> \$panthera -> config\n";
        $out .= "=> \$panthera -> db\n";
        $out .= "=> \$panthera -> logging\n";
        $out .= "=> \$panthera -> locale\n";
        $out .= "=> \$panthera -> template\n";
        $out .= "=> \$panthera -> outputControl\n";
        $out .= "=> \$panthera -> routing\n";
        $out .= "=> \$panthera -> types\n";

        $out .= "\n\n* Useful functions:\n=> debugTools::object_info(), debugTools::r_dump(), debugTools::object_dump(), var_dump(), print_r(), getContentDir(), pantheraUrl(), libtemplate::webrootMerge(), pantheraAutoloader::updateCache()";

        // make some space before prompt
        $out .= "\n\n";

        $this -> panthera -> logging -> output($out, 'phpsh');
    }

    /**
     * Default action
     *
     * @return null
     */

    public function display()
    {
        $this -> setReporting();

        // print generated output before setting printOutput to True
        print($this -> panthera->logging->getOutput());

        if (is_file('content/phpsh.rc.php'))
        {
            $this -> panthera -> logging -> output('Including content/phpsh.rc.php', 'phpsh');
            include 'content/phpsh.rc.php';
        }

        $this -> printWelcomeText();
    }
}

if (strpos(__FILE__, PANTHERA_DIR) !== FALSE)
{
    $object = new _phpshControllerSystem();
    $object -> display();
}

$panthera = pantheraCore::getInstance();