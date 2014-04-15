<?php
/**
  * Template system info and management
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Template system info and management page controller
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class templatesAjaxControllerSystem extends pageController
{
    protected $permissions = array('admin.templates' => array('Templates management', 'templates'));
    
    protected $uiTitlebar = array('Templates management', 'templates');
    
    
    
    /**
      * Running webrootMerge
      *
      * @author Damian Kęska
      * @return null 
      */
    
    public function webrootMergeAction()
    {
        $merge = libtemplate::webrootMerge();
        ajax_exit(array('status' => 'success', 'result' => $merge));
    }
    
    
    
    /**
      * Getting list of templates and its files
      *
      * @author Damian Kęska
      * @return json
      */
  
    public function getTemplatesAction()
    {
        $template = addslashes($_GET['template']);

        if ($template == '')
            $template = False;
    
        return ajax_exit(array('status' => 'success', 'current' => $this -> panthera -> config -> getKey('template'), 'result' => libtemplate::listTemplates($template)));
    }

    
    
    /**
      * Setting template
      *
      * @author Damian Kęska
      * @return null
      */
  
    public function setTemplateAction()
    {
        $template = addslashes($_GET['template']);

        $templates = libtemplate::listTemplates();
        
        unset($templates['admin']);
        unset($templates['admin_mobile']);
        unset($templates['installer']);
        unset($templates['_libs_webroot']);
    
        if (isset($templates[$template]) and $template != 'admin')
        {
            $this -> panthera -> config -> setKey('template', $template, 'string');
            ajax_exit(array('status' => 'success'));
        }
    
        ajax_exit(array('status' => 'failed'));
    }
    
    
    
    /**
      * Execute action
      *
      * @author Damian Kęska
      * @return null 
      */
  
    public function execAction()
    {
        switch ($_GET['name'])
        {
            case 'template_caching':
                if ($_GET['value'] == "true")
                    $this -> panthera -> config -> setKey('template_caching', True, 'bool');
                else
                    $this -> panthera -> config -> setKey('template_caching', False, 'bool');
    
                ajax_exit(array('status' => 'success'));
            break;
    
            case 'template_debugging':
                if ($_GET['value'] == "true")
                    $this -> panthera -> config -> setKey('template_debugging', True, 'bool');
                else
                    $this -> panthera -> config -> setKey('template_debugging', False, 'bool');
    
                ajax_exit(array('status' => 'success'));
            break;
            
            case 'template_cache_lifetime':
                $value = intval($_GET['value']);
    
                if ($value < 0)
                    ajax_exit(array('status' => 'failed'));
    
                 $this -> panthera -> config -> setKey('template_cache_lifetime', $value, 'int');
    
                ajax_exit(array('status' => 'success'));
            break;
    
            case 'validate':
                try {
                    $result = $this -> panthera -> template -> compile($_GET['value']);
                    
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
    
    
    
    /**
      * Main function, display template
      *
      * @author Mateusz Warzyński
      * @author Damian Kęska
      * @return string
      */
      
    public function display()
    {
        $this -> dispatchAction();
        
        $config = array ('template_caching' => $this -> panthera -> config -> getKey('template_caching'),
            'template_cache_lifetime' => $this -> panthera -> config -> getKey('template_cache_lifetime'),
            'template_debugging' => $this -> panthera -> config -> getKey('template_debugging')
        );
                
        $templates = libtemplate::listTemplates();
        
        unset($templates['admin']);
        unset($templates['admin_mobile']);
        unset($templates['installer']);
        unset($templates['_libs_webroot']);
        
        $this -> panthera -> template -> push ('config', $config);
        $this -> panthera -> template -> push ('current_template', $this -> panthera -> config -> getKey('template'));
        $this -> panthera -> template -> push ('templates_list', $templates);
        
        return $this -> panthera -> template -> compile('templates.tpl');
    }
}