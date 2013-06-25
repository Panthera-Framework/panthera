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

foreach (navigation::getHistory() as $key => $value)
    $history[$key] = str_ireplace('_ajax', $panthera->config->getKey('ajax_url'), $value);
  
$panthera -> template -> push('navigation_history', $history);
$panthera -> template -> display('navigation_history.tpl');
pa_exit();
