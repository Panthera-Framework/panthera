<?php
/**
  * Manage plugins
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

    /*--- Ajax pages ---*/

if ($_GET['action'] == 'toggle')
{
    $name = addslashes($_GET['plugin']);

    if ($_GET['value'] == "1")
        $value = (bool)TRUE;
    else
        $value = (bool)FALSE;

    if ($panthera -> switchPlugin($name, $value))
        ajax_exit(array('status' => 'success'));
    else
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot change plugin state, maybe it does not exists anymore')));
}

    /*--- End of ajax pages ---*/

// this info will be passed to template
$pluginsTpl = array();
$plugins = $panthera->getPlugins();

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
        if (array_key_exists('name', $value['info']))
            $title = $value['info']['name'];

        if ($value['info']['meta'] != '')
        {
            if (array_key_exists('author', $value['info']['meta']))
                $author = $value['info']['meta']['author'];

             if (array_key_exists('description', $value['info']['meta']))
                $description = $value['info']['meta']['description'];

             if (array_key_exists('version', $value['info']['meta']))
                $version = $value['info']['meta']['version'];

             if (array_key_exists('configuration', $value['info']['meta']))
                $configuration = $value['info']['meta']['configuration'];
        }
    }

    $pluginsTpl[] = array('name' => $key,
                          'title' => filterInput($title, 'quotehtml'),
                          'path' => $value['include_path'],
                          'author' => filterInput($author, 'quotehtml'),
                          'description' => filterInput($description, 'quotehtml'),
                          'enabled' => $value['enabled'],
                          'version' => filterInput($version, 'quotehtml'),
                          'meta' => $value['info']['meta'],
                          'configuration' => $configuration);
}

$panthera -> locale -> loadDomain('plugins');

$template -> push('plugins', $pluginsTpl);

$titlebar = new uiTitlebar(localize('Manage plugins', 'plugins'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Apps-preferences-plugin-icon.png', 'left');

$template -> display('plugins.tpl');
pa_exit();
?>
