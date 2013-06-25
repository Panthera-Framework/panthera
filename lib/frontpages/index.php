<?php
/**
  * Default front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

// include custom functions to default front controller
if (is_file('content/front.php'))
    require 'content/front.php';

$display = addslashes($_GET['display']);

// here we will include site pages
if (is_file(SITE_DIR. '/content/pages/' .$display. '.php'))
{
    @include(SITE_DIR. '/content/pages/' .$display. '.php');
    pa_exit();
} else {
    define('SITE_ERROR', 404);
    @include(SITE_DIR. '/content/pages/index.php');
}

$template -> display();
$panthera -> finish();
?>
