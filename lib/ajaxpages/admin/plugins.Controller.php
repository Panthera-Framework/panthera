<?php
/**
 * Manage plugins
 *
 * @package Panthera\core\adminUI\plugins
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Plugins pageController class
 *
 * @package Panthera\core\adminUI\plugins
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class pluginsAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Manage plugins', 'plugins'
    );

    protected $permissions = 'admin.plugins';

    /**
     * Toggle plugin (on/off) action
     *
     * @return null
     */

    public function toggleAction()
    {
        $name = addslashes($_GET['plugin']);

        if ($_GET['value'] == "1")
            $value = (bool)TRUE;
        else
            $value = (bool)FALSE;

        if ($this -> panthera -> switchPlugin($name, $value))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot change plugin state, maybe it does not exists anymore')));
    }


    /**
     * Main function
     *
     * @return null
     */

    public function display()
    {
        $this -> dispatchAction();

        $this -> panthera -> locale -> loadDomain('plugins');

        // this info will be passed to template
        $pluginsTpl = array();
        $plugins = $this->panthera->getPlugins();

        foreach ($plugins as $key => $value)
        {
            $title = $key;
            $author = 'unknown';
            $description = '';
            $version = 'unknown';
            $configuration = '';

            // be elegant!
            if ($value['info'] != null)
            {
                if (isset($value['info']['name']))
                    $title = $value['info']['name'];

                if ($value['info']['meta'] != '')
                {
                    if (isset($value['info']['meta']['author']))
                        $author = $value['info']['meta']['author'];

                     if (isset($value['info']['meta']['description']))
                        $description = $value['info']['meta']['description'];

                     if (isset($value['info']['meta']['version']))
                        $version = $value['info']['meta']['version'];

                     if (isset($value['info']['meta']['configuration']))
                        $configuration = $value['info']['meta']['configuration'];
                }
            }

            $pluginsTpl[] = array(
                'name' => $key,
                'title' => filterInput($title, 'quotehtml'),
                'path' => $value['include_path'],
                'author' => filterInput($author, 'quotehtml'),
                'description' => filterInput($description, 'quotehtml'),
                'enabled' => $value['enabled'],
                'version' => filterInput($version, 'quotehtml'),
                'meta' => $value['info']['meta'],
                'configuration' => $configuration
            );
        }

        $this -> panthera -> template -> push('plugins', $pluginsTpl);
        return $this -> panthera -> template -> compile('plugins.tpl');
    }

}