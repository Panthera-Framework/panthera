<?php
/**
  * Show information about Panthera
  *  
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

$tpl = '_ajax_panthera.tpl';

$text = localize('<b>Panthera</b> is a powerful framework intended to create advanced, fast and extendable web applications.\nSupport for multiple languages is provided by <b>GNU Gettext</b> which makes Panthera translations easy to understand - its possible to use simple desktop applications such as poedit, kwrite, vim, nano, gedit or web applications to translate Panthera websites.\n\n<b>Model based data management can reduce work on a web application even by 80%!</b>\nInspired by Wordpress, <b>Panthera is easy to understand for all PHP programmers</b>, because of its simple construction - no obligation to create routers, models, views and controls. <i><b>Just make it simple as possible using Panthera goods.</i></b>\n\nPanthera is developed by Damian Kęska (main developer, programmer, project coordinator) and Mateusz Warzyński (programmer).\nDistributed under terms of <b>LGPLv3</b> licence. Included libraries may be on other free licences.', 'panthera');

$text = str_replace('\n', "\n<br>", $text);

$goods = array();

$goods[] = localize('Multiple database support - we use PDO with own wrapper (so, the PDO can be replaced)', 'panthera');
$goods[] = localize('Plugins - everything should be extendable', 'panthera');
$goods[] = localize('Smarty! Templates are powerful and fast', 'panthera');
$goods[] = localize('Multiple translations support using GNU Gettext', 'panthera');
$goods[] = localize('Model based data management using built-in classes allows easily to turn database table in to a class and allow save using assignment operator', 'panthera');
$goods[] = localize('Built-in user management features full users and groups management including session storage', 'panthera');
$goods[] = localize('Ajax, JSON and jQuery based', 'panthera');
$goods[] = localize('Configuration management, custom data types validation', 'panthera');
$goods[] = localize('Shell tools for developers allows easily debugging application and compile translations in realtime', 'panthera');

$template -> push('goods', $goods);
$template -> push('text', $text);
