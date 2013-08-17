<?php
/**
  * Default front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
  
if (!is_file('content/app.php'))
{
    header('Location: install.php');
    exit;
}

require 'content/app.php';

// include custom functions to default front controller
if (is_file('content/front.php'))
    require 'content/front.php';

$display = str_replace('/', '', addslashes($_GET['display']));
$template -> setTemplate($panthera->config->getKey('template'));
$path = False;

if (!defined('PAGES_DISABLE_LIB'))
{
    $path = getContentDir('/pages/' .$display. '.php');
} else {
    if (is_dir(SITE_DIR. '/content/pages/' .$display. '.php'))
        $path = SITE_DIR. '/content/pages/' .$display. '.php';
}

// here we will include site pages
if ($path)
{
    @include($path);
    pa_exit();
} else {
    define('SITE_ERROR', 404);
    @include(SITE_DIR. '/content/pages/index.php');
}

$template -> display();
$panthera -> finish();
?>
