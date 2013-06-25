<?php
/**
  * Front controller for CLI app - phpsh
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @see http://www.phpsh.org/
  * @license GNU Affero General Public License 3, see license.txt
  */
  
# include app config and libs
require 'content/app.php';

if (PANTHERA_MODE == 'cgi')
    die('This script is not avaliable in CGI mode.');

error_reporting(E_ALL);

// print logging output directly to console
$panthera -> logging -> printOutput = True;

// print generated output before setting printOutput to True
print($panthera->logging->getOutput());
