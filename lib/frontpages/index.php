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

// front controllers utils
include PANTHERA_DIR. '/frontController.class.php';
    
// enable frontside panels
frontsidePanels::init();


// a small posibility to change "?display" to other param name
$displayVar = 'display';

if (isset($config['indexDisplayVar']))
    $displayVar = $config['indexDisplayVar'];

$display = str_replace('/', '', addslashes($_GET[$displayVar]));
$template -> setTemplate($panthera->config->getKey('template'));
$path = False;

if (!defined('PAGES_DISABLE_LIB') and !$panthera -> config -> getKey('front.index.disablelib', 0, 'bool', 'frontindex'))
{
    $path = getContentDir('/pages/' .$display. '.php');
    
    // disabled pages can be empty pages
    if (filesize($path) < 8)
    {
        $path = false;
    }
    
} else {
    if (is_dir(SITE_DIR. '/content/pages/' .$display. '.php'))
        $path = SITE_DIR. '/content/pages/' .$display. '.php';
}

// here we will include site pages
if ($path)
{
    include $path;
    
    $controllerName = $display. 'Controller';
    
    if (class_exists($display. 'ControllerCore'))
        $controllerName = $display. 'ControllerCore';
    
    if (frontController::$searchFrontControllerName)
        $controllerName = frontController::$searchFrontControllerName;
    
    if (class_exists($controllerName))
    {
        $controller = new $controllerName;
        print($controller -> display());
    }
    
    pa_exit();
} else {
    define('SITE_ERROR', 404);
    
    if (is_file(SITE_DIR. '/content/pages/index.php'))
        include(SITE_DIR. '/content/pages/index.php');
}

$template -> display();
pa_exit();
?>
