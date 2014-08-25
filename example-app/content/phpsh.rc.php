<?php
/**
 * RC file for _phpsh front controller - use this file to add custom startup PHP code
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @see http://www.phpsh.org/
 * @license GNU Affero General Public License 3, see license.txt
 */
 
// do something on _phpsh.php startup
$captcha = captcha::createInstance();
