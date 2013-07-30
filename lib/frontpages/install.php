<?php
/**
  * Installer front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

// include step
$step = addslashes($_GET['step']);

if ($step == '')
    $step = 'index';

if (!$panthera -> moduleExists('installer/' .$step))
{
    $step = 'error_no_step';
}    

// initialize installer
$panthera -> importModule('pantherainstaller');
$installer = new pantheraInstaller($panthera);

// template options
$panthera -> template -> setTemplate('installer');
$panthera -> template -> setTitle('Panthera Installer');

// include step
define('PANTHERA_INSTALLER', True);
$panthera -> importModule('installer/' .$step);
