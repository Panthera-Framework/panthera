<?php
/**
 * Panthera Framework 2 bootstrap
 *
 * @package Panthera\bootstrap
 * @author Damian Kęska <damian@pantheraframework.org>
 */
define('PANTHERA_FRAMEWORK_2', true);
define('PANTHERA_FRAMEWORK_PATH', __DIR__);

/**
 * Detect application path
 */
$controllerPath = '';
$bTrace = debug_backtrace(false, 4);

if (array_key_exists('file', $bTrace[count($bTrace)-1]))
{
    $controllerPath = $bTrace[count($bTrace)-1]['file'];
}

// support for CLI applications ran from Panthera Framework's "/bin" directory
if (strtolower(PHP_SAPI) == 'cli' && (strpos($controllerPath, __DIR__. '/Binaries/') === 0 || isset($_SERVER['APP_PATH'])))
{
    $cwd = getcwd();

    if (isset($_SERVER['APP_PATH']))
    {
        $cwd = $_SERVER['APP_PATH'];
    }

    $controllerPath = $cwd. '/index.php';
    require_once $cwd. '/.content/app.php';
}

// in case of PHPUnit test we must change $controllerPath to appPath
elseif (stripos($_SERVER['SCRIPT_FILENAME'], 'phpunit') !== false)
{
    foreach ($bTrace as $array)
    {
        if (basename($array['file']) == 'app.php')
        {
            $controllerPath = str_replace('/.content/app.php', '/index.php', $array['file']);
            break;
        }
    }
}


require_once __DIR__. '/Components/Kernel/Framework.php';
require_once __DIR__. '/Components/Autoloader/Autoloader.php';
spl_autoload_register('Panthera\Components\Autoloader\Autoloader::loadClass');

if (!isset($defaultConfig))
{
    throw new \Panthera\Classes\BaseExceptions\InvalidConfigurationException('Cannot find $defaultConfig variable', 'FW_CONFIG_NOT_FOUND');
}

$app = Panthera\Components\Kernel\framework::getInstance($controllerPath);
$app->setup($defaultConfig);