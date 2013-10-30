<?php
/**
  * Template system info and management
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_manage_templates')) {
    $noAccess = new uiNoAccess;
    $noAccess -> display();
}

/**
  * Running webrootMerge
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'webrootMerge')
{
    $merge = libtemplate::webrootMerge();
    ajax_exit(array('status' => 'success', 'result' => $merge));

/**
  * Getting list of templates and its files
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'getTemplates') {
    $template = addslashes($_GET['template']);

    if ($template == '')
        $template = False;

    return ajax_exit(array('status' => 'success', 'current' => $panthera -> config -> getKey('template'), 'result' => libtemplate::listTemplates($template)));

/**
  * Setting template
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'setTemplate') {
    $template = addslashes($_GET['template']);

    $templates = libtemplate::listTemplates();
    
    unset($templates['admin']);
    unset($templates['admin_mobile']);
    unset($templates['installer']);
    unset($templates['_libs_webroot']);

    if (isset($templates[$template]) and $template != 'admin')
    {
        $panthera -> config -> setKey('template', $template, 'string');
        ajax_exit(array('status' => 'success'));
    }

    ajax_exit(array('status' => 'failed'));
    
/**
  * Execute action
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'exec') {

    switch ($_GET['name'])
    {
        case 'template_caching':
            if ($_GET['value'] == "true")
                $panthera -> config -> setKey('template_caching', True, 'bool');
            else
                $panthera -> config -> setKey('template_caching', False, 'bool');

            ajax_exit(array('status' => 'success'));
        break;

        case 'template_debugging':
            if ($_GET['value'] == "true")
                $panthera -> config -> setKey('template_debugging', True, 'bool');
            else
                $panthera -> config -> setKey('template_debugging', False, 'bool');

            ajax_exit(array('status' => 'success'));
        break;
        
        case 'template_cache_lifetime':
            $value = intval($_GET['value']);

            if ($value < 0)
                ajax_exit(array('status' => 'failed'));

             $panthera -> config -> setKey('template_cache_lifetime', $value, 'int');

            ajax_exit(array('status' => 'success'));
        break;

        case 'validate':
            try {
                $result = $panthera -> template -> compile($_GET['value']);
                
                if ($result != '')
                    ajax_exit(array('status' => 'success', 'message' => localize('Template syntax is valid', 'templates')));
                    
            } catch (Exception $e) {
                ajax_exit(array('status' => 'failed', 'message' => $e -> getMessage()));
            }
            
            ajax_exit(array('status' => 'failed', 'message' => localize('Error, check if template file name is correct', 'templates')));
        break;
        
        case 'clear_cache':
            $panthera -> template -> clearCache();
            ajax_exit(array('status' => 'success', 'message' => localize('Done')));
        break;
    }

}

$config = array ('template_caching' => $panthera -> config -> getKey('template_caching'),
                 'template_cache_lifetime' => $panthera -> config -> getKey('template_cache_lifetime'),
                 'template_debugging' => $panthera -> config -> getKey('template_debugging')
                );
                
$templates = libtemplate::listTemplates();

unset($templates['admin']);
unset($templates['admin_mobile']);
unset($templates['installer']);
unset($templates['_libs_webroot']);
                
$titlebar = new uiTitlebar(localize('Templates management', 'templates'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Icon-template.png', 'left');

$panthera -> template -> push ('config', $config);
$panthera -> template -> push ('current_template', $panthera -> config -> getKey('template'));
$panthera -> template -> push ('templates_list', $templates);
$panthera -> template -> display('templates.tpl');
pa_exit();
