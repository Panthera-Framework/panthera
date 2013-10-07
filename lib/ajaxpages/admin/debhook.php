<?php
/**
  * Show hooked functions
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

#$builtInClasses = @json_decode(file_get_contents($panthera->config->getKey('url'). '/_php_helper.php?code=' .$panthera->config->getKey('internal_passwd')));

if (!getUserRightAttribute($user, 'can_see_debhook')) {
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
}

$panthera -> locale -> loadDomain('debhook');

if ($_GET['action'] == 'list')
{
    /* List of classes and functions */
    $functions = get_defined_functions();
    $userFunctions = array();

    foreach ($functions['user'] as $key => $value)
    {
        $reflection = new ReflectionFunction($value);

        // parameters list
        $paramsTmp = $reflection -> getParameters();
        $params = '';

        foreach ($paramsTmp as $k)
            $params .= $k->getName(). ', ';

        $params = rtrim($params, ', ');

        if (isset($_GET['search']))
        {
            if (strpos($_GET['search'], $value) === -1)
                continue;
        }

        $userFunctions[] = array('type' => 'function', 'name' => $value, 'filename' => $reflection->getFileName(), 'declaration' => $reflection->getFileName(). ':' .$reflection->getStartLine(), 'params' => $params, 'startline' => ($reflection->getStartLine()-10), 'endline' => ($reflection->getEndLine()+2));
    }

    $arrayFunctions = array();

    $classes = get_declared_classes();

    foreach ($classes as $className)
    {
        // we dont want to display built-in PHP classes
        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass -> isUserDefined())
            continue;

        unset($reflectionClass);

        $arrayFunctions[] = array('type' => 'class', 'name' => $className);
        $methods = get_class_methods($className); // get all class methods


        foreach ($methods as $methodName)
        {
            $reflection = new ReflectionMethod($className, $methodName);

            // start line and file name
            $fileName = $reflection->getFileName();
            $startLine = intval($reflection->getStartLine());

            if ($fileName == '')
                $fileName = 'unknown';

            if (isset($_GET['search']))
            {
                if (strpos($_GET['search'], $methodName) === -1)
                    continue;
            }

             // parameters list
            $paramsTmp = $reflection -> getParameters();
            $params = '';

            foreach ($paramsTmp as $k)
                $params .= $k->getName(). ', ';

            $params = rtrim($params, ', ');

            // end of parameters list

            $arrayFunctions[] = array('type' => 'method', 'name' => $className. ' -> ' .$methodName, 'filename' => $fileName, 'declaration' => $fileName. ':' .$startLine, 'params' => $params, 'startline' => ($reflection->getStartLine()-10), 'endline' => ($reflection->getEndLine()+2));
        }
    }


    //$arrayFunctions = array_reverse($arrayFunctions);

    $arrayFunctions = array_merge($userFunctions, $arrayFunctions);
    $template -> push('functions', $arrayFunctions);
    $template -> push('action', 'list');
    $template -> display('debhook.tpl');
    pa_exit();
}



$hookOptions = $panthera -> getAllHooks();
$list = array();

foreach ($hookOptions as $key => $hooks)
{
    foreach ($hooks as $k => $value)
    {
        if (is_array($value))
        {
            if (get_class($value[0]) === False)
                continue;
            
            $reflection = new ReflectionMethod(get_class($value[0]), $value[1]);
            $name = get_class($value[0]). ' -> ' .$value[1];
        } else {
            $reflection = new ReflectionFunction($value);
            $name = $value;
        }

        // parameters list
        $paramsTmp = $reflection -> getParameters();
        $params = '';

        foreach ($paramsTmp as $k)
            $params .= $k->getName(). ', ';

        $params = rtrim($params, ', ');

        $array[] = array('hook' => $key, 'function' => $name, 'filename' => $reflection->getFileName(), 'declaration' => $reflection->getFileName(). ':' .$reflection->getStartLine(), 'params' => $params, 'startline' => ($reflection->getStartLine()-10), 'endline' => ($reflection->getEndLine()+2));
    }
}

$titlebar = new uiTitlebar(localize('Plugins debugger', 'settings'));
$panthera -> template -> push('hooks', $array);
$panthera -> template -> display('debhook.tpl');
pa_exit();

