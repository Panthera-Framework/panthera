<?php
/**
  * Index step in Panthera installer
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
if (!defined('PANTHERA_INSTALLER'))
    return False;
    
// we will use this ofcourse
global $panthera;
global $installer;

if (!is_dir(SITE_DIR. '/css') or !is_dir(SITE_DIR. '/js') or !is_dir(SITE_DIR. '/images'))
    $panthera -> template -> webrootMerge();

// we will check here the PHP version and required basic modules

var_dump($panthera -> session -> detectBrowser());
