<?php
/**
  * Merge serialized arrays and json files
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!checkUserPermissions($user, True))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    $panthera->finish();
    pa_exit();
}

$panthera -> locale -> setDomain('debug');
$panthera -> importModule('filesystem');

/**
  * Merge files
  *
  * @return array 
  * @author Damian Kęska
  */

function mergePHPS()
{
    global $panthera;
    
    $mergephpsFiles = $panthera -> session -> get('mergephps_files');
    $array = array();
    
    foreach ($mergephpsFiles as $key => $value)
    {
        $json = @json_decode($value);
        
        if (is_array($json))
            $array = array_merge($array, $json);
            
        $serialized = unserialize($value);
        
        if (is_array($serialized))
            $array = array_merge($array, $serialized);
            
        if (!is_array($serialized) and !is_array($json))
            unset($mergephpsFiles[$key]);
    }
    
    $panthera -> session -> set('mergephps_files', $mergephpsFiles);
    
    return $array;
}

/**
  * Get file names of files we want to merge
  *
  * @return array 
  * @author Damian Kęska
  */

function getFileNames()
{
    global $panthera;
    
    $mergephpsFiles = $panthera -> session -> get('mergephps_files');
    
    $fileNames = array();
    
    foreach ($mergephpsFiles as $file => $content)
    {
        $fileNames[$file] = strlen($mergephpsFiles[$file]);
    }
    
    return $fileNames;
}

//var_dump($panthera -> session -> get('mergephps_files'));

/**
  * Change output type
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'outputType')
{
    switch ($_POST['type'])
    {
        case 'json':
            $panthera -> session -> set('mergephps_type', 'json');
        break;
        
        default:
            $panthera -> session -> set('mergephps_type', 'serialize');
        break;
    }
    
    if ($panthera -> session -> get('mergephps_type') == 'json')
    {
        if (version_compare(phpversion(), '5.4.0', '>'))
            $html = json_encode(mergePHPS(), JSON_PRETTY_PRINT);
        else
            $html = json_encode(mergePHPS());
    } else
        $html = serialize(mergePHPS());
    
    ajax_exit(array('status' => 'success', 'files' => getFileNames(), 'html' => $html));
}

/**
  * Remove file from list
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'removeFile')
{
    $mergephpsFiles = $panthera -> session -> get('mergephps_files');
    unset($mergephpsFiles[$_POST['fileName']]);
    $panthera -> session -> set('mergephps_files', $mergephpsFiles);
    
    if ($panthera -> session -> get('mergephps_type') == 'json')
    {
        if (version_compare(phpversion(), '5.4.0', '>'))
            $html = json_encode(mergePHPS(), JSON_PRETTY_PRINT);
        else
            $html = json_encode(mergePHPS());
    } else
        $html = serialize(mergePHPS());
    
    ajax_exit(array('status' => 'success', 'files' => getFileNames(), 'html' => $html));     
}

/**
  * Upload a new file
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'upload')
{
    $mergephpsFiles = $panthera -> session -> get('mergephps_files');
    $parsed = parseEncodedUpload($_POST['file'], True);
    
    if ($parsed['mime'] != 'application/x-php' and $parsed['mime'] != '')
        ajax_exit(array('status' => 'failed', 'message' => localize('Not a valid serialized array or json content', 'debug')));
    
    // perform merge
    $mergephpsFiles[substr($_POST['fileName'], 0, 32)] = $parsed['content'];
    $panthera -> session -> set('mergephps_files', $mergephpsFiles);
    
    if ($panthera -> session -> get('mergephps_type') == 'json')
    {
        if (version_compare(phpversion(), '5.4.0', '>'))
            $html = json_encode(mergePHPS(), JSON_PRETTY_PRINT);
        else
            $html = json_encode(mergePHPS());
    } else
        $html = serialize(mergePHPS());
    
    ajax_exit(array('status' => 'success', 'files' => getFileNames(), 'html' => $html));    
}

if (!$panthera -> session -> exists('mergephps_files'))
    $panthera -> session -> set('mergephps_files', array());

$mergephpsFiles = $panthera -> session -> get('mergephps_files');

$fileNames = array();
    
foreach ($mergephpsFiles as $file => $content)
{
    $fileNames[$file] = strlen($mergephpsFiles[$file]);
}

if ($panthera -> session -> get('mergephps_type') == 'json')
{
    if (version_compare(phpversion(), '5.4.0', '>'))
        $html = json_encode(mergePHPS(), JSON_PRETTY_PRINT);
    else
        $html = json_encode(mergePHPS());
} else
    $html = serialize(mergePHPS());

$panthera -> template -> push('popup', $_GET['popup']);
$panthera -> template -> push('files', $fileNames);
$panthera -> template -> push('result', $html);
$panthera -> template -> display('mergephps.tpl');
pa_exit();
