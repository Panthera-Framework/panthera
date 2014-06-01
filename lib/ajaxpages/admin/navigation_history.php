<?php
/**
  * Navigation history page
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
$history = array();
$panthera -> template -> push('navigation_history', navigation::getHistory());
$panthera -> template -> display('navigation_history.tpl');
pa_exit();
