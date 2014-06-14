<?php
/**
 * Session configuration page
 *
 * @package Panthera\core\adminUI\settings
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */


/**
 * Session configuration page controller
 *
 * @package Panthera\core\adminUI\settings
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class settings_mceAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.settings.mce' => array('Text editor settings', 'settings'),
        'admin.conftool' => array('Advanced system configuration editor', 'conftool'),
    );

    protected $uiTitlebar = array(
        'Text editor settings', 'settings'
    );



    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        $this -> panthera -> config -> getKey('cookie_encrypt', 1, 'bool');
        $this -> panthera -> locale -> loadDomain('settings');
        $this -> panthera -> locale -> loadDomain('mce');

        $editors = array();
        $mceConfig = uiMce::getConfiguration();

        foreach (uiMce::getAvaliableEditors() as $editor)
        {
            $editors[$editor] = $editor;
        }

        // default values
        //$this -> panthera ->config -> getKey('mce.css', '{$PANTHERA_URL}/css/style.css', 'string', 'mce');
        $this -> panthera ->config -> getKey('mce.default', 'tinymce', 'string', 'mce');

        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('mce');

        $config -> add('mce.default', localize('Default Wysiwyg editor', 'mce'), $editors);
        $config -> add('mce.css', localize('Style CSS', 'mce'));
        $config -> setDescription('mce.css', localize('Address to CSS style to use inside of text editor', 'mce'));

        // add mce specific configuration
        foreach ($mceConfig['configuration'] as $key => $value)
        {
            if (!$value['values'])
                $value['values'] = '';

            $config -> add('mce.' .uiMce::getActiveEditor(). '.' .$key, localize($key, 'mce'), $value['values']);
        }

        $result = $config -> handleInput($_POST);


        if (is_array($result))
            ajax_exit(array(
                'status' => 'failed',
                'message' => $result['message'][1], 'field' => $result['field'],
            ));

        elseif ($result === True)
            ajax_exit(array(
                'status' => 'success',
            ));


        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}