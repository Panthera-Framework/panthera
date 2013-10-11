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

if (!getUserRightAttribute($user, 'can_see_debhook')) {
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
}

$search = $_GET['query'];

//if ($_GET['action'] == 'list')
//{
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

        if ($search)
        {
            if (stripos($value, $search) === False)
                continue;
        }

        $userFunctions['function_' .$value] = array('type' => 'function', 'name' => $value, 'filename' => $reflection->getFileName(), 'declaration' => $reflection->getFileName(). ':' .$reflection->getStartLine(), 'params' => $params, 'startline' => ($reflection->getStartLine()-10), 'endline' => ($reflection->getEndLine()+2));
    }

    $arrayFunctions = array();

    $classes = get_declared_classes();
    $classesFuncs = array(); // count of functions per class

    foreach ($classes as $className)
    {
        // we dont want to display built-in PHP classes
        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass -> isUserDefined())
            continue;

        unset($reflectionClass);

        $arrayFunctions['class_' .$className] = array('type' => 'class', 'name' => $className);
        $methods = get_class_methods($className); // get all class methods


        foreach ($methods as $methodName)
        {
            $reflection = new ReflectionMethod($className, $methodName);

            // start line and file name
            $fileName = $reflection->getFileName();
            $startLine = intval($reflection->getStartLine());

            if ($fileName == '')
                $fileName = 'unknown';

            if ($search)
            {
                if (stripos($methodName, $search) === False)
                    continue;
                    
                $classesFuncs[$className]++;
            }

             // parameters list
            $paramsTmp = $reflection -> getParameters();
            $params = '';

            foreach ($paramsTmp as $k)
                $params .= $k->getName(). ', ';

            $params = rtrim($params, ', ');

            // end of parameters list
            $arrayFunctions['method_' .$methodName] = array('type' => 'method', 'name' => $className. ' -> ' .$methodName, 'filename' => $fileName, 'declaration' => $fileName. ':' .$startLine, 'params' => $params, 'startline' => ($reflection->getStartLine()-10), 'endline' => ($reflection->getEndLine()+2));
        }
    }
    
    if ($search)
    {
        foreach ($arrayFunctions as $objectName => $data)
        {
            if (strpos($objectName, 'class_') === 0)
            {
                $realName = str_replace('class_' .$objectName, '', $objectName);
                
                if (!isset($classesFuncs[$className]))
                {
                    unset($arrayFunctions[$realName]);
                }
            }
        }
    }


    //$arrayFunctions = array_reverse($arrayFunctions);

    $arrayFunctions = array_merge($userFunctions, $arrayFunctions);
    $template -> push('functions', $arrayFunctions);
//}


/**
  * Get list of all defined hooks
  *
  * @author Damian Kęska
  */

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

$sBar = new uiSearchbar('uiTop');
//$sBar -> setMethod('POST');
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=debhook&cat=admin');
$sBar -> navigate(True);

$titlebar = new uiTitlebar(localize('Plugins debugger', 'settings'));
$panthera -> template -> push('hooks', $array);
$panthera -> template -> display('debhook.tpl');
pa_exit();

