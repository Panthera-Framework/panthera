<?php
/**
  * Database configuration
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

$panthera -> importModule('rwjson');

$templates = array();
$tpl = $panthera -> template -> listTemplates();

if ($_GET['action'] == 'createView')
{
    // check if template exists
    if (!isset($tpl[$_GET['template']]))
        ajax_exit(array('status' => 'failed'));
        
    if (strpos($_GET['view'], ','))
    {
        $views = explode(',', str_replace('/', '', $_GET['view']));
        $viewName = $views[0];
    } else {
        $viewName = str_replace('/', '', $_GET['view']);
    }
    
    $panthera -> importModule('rwjson');
    $newTemplatePath = SITE_DIR. '/content/templates/' .$_GET['template']. '_' .$viewName;
    
    $baseTemplateConfig = new writableJSON(getContentDir('templates/' .$_GET['template']. '/config.json'));
    $baseTemplateConfig -> set($viewName. '_template', $_GET['template']. '_' .$viewName);
    
    // if there is more than one view
    if (isset($views))
    {
        foreach ($views as $view)
        {
            $baseTemplateConfig -> set($view. '_template', $_GET['template']. '_' .$viewName);
        }
    }
    
    $baseTemplateConfig -> save();

    // create empty directories
    mkdir($newTemplatePath);
    mkdir($newTemplatePath. '/templates');
    mkdir($newTemplatePath. '/webroot');
    
    // create a new config.json file
    $fp = fopen($newTemplatePath. '/config.json', 'w');
    fwrite($fp, json_encode(array(
                            'index' => 'index.tpl',
                            'desktop_template' => $_GET['template'],
                            $viewName => True
                            )));
                            
    fclose($fp);
                
    // create example template            
    $fp = fopen($newTemplatePath. '/templates/index.tpl', 'w');
    fwrite($fp, 'Hello world from Panthera Framework, this is an example page for ' .$viewName. ' view');
    fclose($fp);
    pa_redirect('install.php');
} elseif ($_GET['action'] == 'connectView') {

    // check if template exists
    if (!isset($tpl[$_GET['template']]))
        ajax_exit(array('status' => 'failed'));
        
    $baseTemplateConfig = new writableJSON(getContentDir('templates/' .$_GET['template']. '/config.json'));
    $baseTemplateConfig -> set($_GET['to']. '_template', $_GET['template']. '_' .$_GET['from']);
    $baseTemplateConfig -> save();
    pa_redirect('install.php');
} elseif ($_GET['action'] == 'setDefaultTemplate') {

    // check if template exists
    if (!isset($tpl[$_GET['template']]))
        ajax_exit(array('status' => 'failed'));
        
    $panthera -> config -> setKey('template', $_GET['name']);
    $panthera -> config -> save();
} elseif ($_GET['action'] == 'createNewTemplate') {
    $name = str_replace('/', '', $_GET['name']);
    
    // new template will be placed
    $newTemplatePath = SITE_DIR. '/content/templates/' .$name;
    
    if (!is_dir($newTemplatePath) and strlen($name) > 2)
    {
        // create empty directories
        mkdir($newTemplatePath);
        mkdir($newTemplatePath. '/templates');
        mkdir($newTemplatePath. '/webroot');
        
        // create a new config.json file
        $fp = fopen($newTemplatePath. '/config.json', 'w');
        fwrite($fp, json_encode(array(
                                'index' => 'index.tpl'
                                )));
                                
        fclose($fp);
        
        // create example template            
        $fp = fopen($newTemplatePath. '/templates/index.tpl', 'w');
        fwrite($fp, 'Hello world from Panthera Framework, this is an example page for desktop view');
        fclose($fp);
    }
}

foreach ($tpl as $key => $value)
{
    if ($key == 'installer' or substr($key, 0, 5) == 'admin')
    {
        continue;
    }
    
    $config = $panthera -> template -> getTemplateConfig($key);
    
    if (!is_array($config))
        continue;
        
    // dont show mobile and tablet templates, just only show desktop templates
    if ($config['mobile'] == True or isset($config['desktop_template']))
        continue;
        
    $templates[$key] = array('mobile' => False, 'tablet' => False, 'active' => False);

    if (isset($config['mobile_template']))
        $templates[$key]['mobile'] = True;
        
    if (isset($config['tablet_template']))
        $templates[$key]['tablet'] = True;

    // check if template is currently set as site template        
    if ($panthera -> config -> getKey('template') == $key)
        $templates[$key]['active'] = True;
}

if ($panthera->config->getKey('template'))
{
    if (isset($tpl[$panthera->config->getKey('template')]))
    {
        $installer -> enableNextStep();
    }
}

$panthera -> template -> push ('templates', $templates);
$installer -> template = 'templates';
