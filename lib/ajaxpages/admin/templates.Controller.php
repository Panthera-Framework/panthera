<?php
/**
 * Template system info and management
 *
 * @package Panthera\core\system\templates
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */


/**
 * Template system info and management page controller
 *
 * @package Panthera\core\system\templates
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
                    if ($this -> panthera -> varCache)
                    {
                        $this -> panthera -> varCache -> set('pa-login.system.loginkey', array(
                            'key' => generateRandomString(128),
                            'userID' => $this -> panthera -> user -> id,
                        ), 120);
                    }

                    $http = new httplib;
                    $key = $this -> panthera -> varCache -> get('pa-login.system.loginkey');

                    $result = $http -> get(pantheraUrl('{$PANTHERA_URL}/_ajax.php?_bypass_x_requested_with&_system_loginkey=' .$key['key']. '&display=templates&cat=admin&action=exec&name=validateProxy&template=' .$_GET['template']. '&value=' .$_GET['value']));
                    $http -> close();

                    $result = '';

                    if (strpos($result, 'PHP') === False)
                        ajax_exit(array(
                            'status' => 'success',
                            'message' => localize('Template syntax is valid', 'templates'),
                        ));
                    else
                        ajax_exit(array(
                            'status' => 'failed',
                            'message' => $result,
                        ));

                } catch (Exception $e) {
                    ajax_exit(array(
                        'status' => 'failed',
                        'message' => $e -> getMessage(),
                    ));
                }

                ajax_exit(array('status' => 'failed', 'message' => localize('Error, check if template file name is correct', 'templates')));
            break;

            case 'clear_cache':
                $panthera -> template -> clearCache();
                ajax_exit(array('status' => 'success', 'message' => localize('Done')));
            break;

            case 'validateProxy':
                try {
                    print($this -> panthera -> template -> compile($_GET['value'], False, '', $_GET['template']));
                } catch (Exception $e) {
                    print($e -> getMessage());
                }
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
        unset($templates['_system']);
        unset($templates['_mails']);
        unset($templates['_docs']);

        $this -> panthera -> template -> push ('config', $config);
        $this -> panthera -> template -> push ('current_template', $this -> panthera -> config -> getKey('template'));
        $this -> panthera -> template -> push ('templates_list', $templates);

        return $this -> panthera -> template -> compile('templates.tpl');
    }
}