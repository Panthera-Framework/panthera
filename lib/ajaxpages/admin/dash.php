<?php
/**
  * Home site
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_see_dash')) {
    $template->display('no_access.tpl');
    pa_exit();
}

// dash have it's own configuration section
$panthera -> config -> loadSection('dash');

$panthera -> locale -> loadDomain('dash');
$panthera -> template -> push('widgetsUnlocked', 0);


/**
  * Remove widget from dashboard
  *
  * @param string $widget
  * @author Damian Kęska
  */

if ($_GET['action'] == 'remove')
{
    $widgets = $panthera -> config -> getKey('dash.widgets');

    // disable widget
    if(array_key_exists($_GET['widget'], $widgets))
    {
        $widgets[$_GET['widget']] = False;
    }

    $panthera -> config -> setKey('dash.widgets', $widgets, 'array', 'dash');
    $panthera -> template -> push('widgetsUnlocked', 1);

/**
  * Add a widget from /modules/dash/ directory or builtin (gallery or lastLogged)
  *
  * @param string $widget
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'add') {

    $widget = addslashes(str_replace('/', '', $_GET['widget']));

    if (is_file(PANTHERA_DIR. '/modules/dash/' .$widget. '.widget.php') or is_file(SITE_DIR. '/content/modules/dash/' .$widget. '.widget.php') or $widget == 'gallery' or $widget == 'lastLogged')
    {
        $widgets = $panthera -> config -> getKey('dash.widgets');
        $widgets[$widget] = True;
        $panthera -> config -> setKey('dash.widgets', $widgets, 'array', 'dash');
    }

    $panthera -> template -> push('widgetsUnlocked', 1);
}

$defaults = array();

$defaults[] = array('link' => '{$PANTHERA_URL}', 'name' => localize('Front page', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/home.png');
$defaults[] = array('link' => '?display=settings&cat=admin', 'name' => localize('Settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/settings.png' , 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=debug&cat=admin', 'name' => localize('Debugging center'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/developement.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=users&cat=admin', 'name' => localize('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=mailing&cat=admin', 'name' => localize('Mailing', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=gallery&cat=admin', 'name' => localize('Gallery'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/gallery.png', 'linkType' => 'ajax');
$defaults[] = array('link' => 'createPopup(\'_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback\', 1300, 550);', 'name' => localize('Uploads', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/uploads.png', 'linkType' => 'onclick');
$defaults[] = array('link' => '?display=contact&cat=admin', 'name' => localize('Contact'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/contact.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=custom&cat=admin', 'name' => localize('Custom pages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'linkType' => 'ajax');
//$menu[] = array('link' => '?display=newsletter&cat=admin', 'name' => localize('Newsletter'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Newsletter.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=messages&cat=admin', 'name' => localize('Quick messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/messages.png', 'linkType' => 'ajax');

$defaults[] = array('link' => '?display=users&cat=admin', 'name' => localize('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');
//$defaults[] = array('link' => '?display=users&cat=admin&action=account', 'name' => localize('My account', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/user.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=database&cat=admin', 'name' => localize('Database management', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png' , 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=cache&cat=admin', 'name' => localize('Cache management', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/cache.png' , 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=leopard&cat=admin', 'name' => localize('Package management', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/package.png' , 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=settings&cat=admin&action=system_info', 'name' => localize('Informations about system', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/system.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=conftool&cat=admin', 'name' => localize('Configuration editor', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/config.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=ajaxpages&cat=admin', 'name' => localize('Index of ajax pages', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=shellutils&cat=admin', 'name' => localize('Shell utils', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-yakuake-icon.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=errorpages&cat=admin', 'name' => localize('System error pages', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=menuedit&cat=admin', 'name' => localize('Menu editor', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=locales&cat=admin', 'name' => localize('Language settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/locales.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=plugins&cat=admin', 'name' => ucfirst(localize('plugins', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-preferences-plugin-icon.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=templates&cat=admin', 'name' => ucfirst(localize('templates', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Icon-template.png', 'linkType' => 'ajax');
$defaults[] = array('link' => '?display=langtool&cat=admin', 'name' => ucfirst(localize('translates', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/langtool.png', 'linkType' => 'ajax');

$menuDB = $panthera -> config -> getKey('dash.items', $defaults, 'array', 'dash');
$maxItems = $panthera -> config -> getKey('dash.maxItems', 16, 'int', 'dash');
$menu = array();

switch ($_GET['menu'])
{
    case 'more':
        $menu = $menuDB;
        $menu[] = array('link' => '?display=dash&cat=admin', 'name' => localize('Less', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/tango-icon-theme/Go-previous.svg' , 'linkType' => 'ajax');
    break;

    case '':
        $_GET['menu'] = 'main';
        
        foreach ($menuDB as $num => $item)
        {
            if ($num == $maxItems)
            {
                break;
            }
            
            $menu[] = $item;
        }
        
        $menu[] = array('link' => '?display=dash&cat=admin&menu=more', 'name' => localize('More', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/tango-icon-theme/Go-next.svg' , 'linkType' => 'ajax');
        
        // main menu, there are predefined variables
        $panthera -> template -> push('showWidgets', True);
    break;
}

list($menu, $category) = $panthera -> get_filters('dash_menu', array($menu, $_GET['menu']));

/**
  * Main screen
  *
  * @author Damian Kęska
  */

if ($panthera->config->getKey('dash.enableWidgets', 1, 'bool', 'dash'))
{
    $settings = $panthera -> config -> getKey('dash.widgets', array('gallery' => True, 'lastLogged' => True), 'array', 'dash');
    $widgets = False;
    $enabledWidgets = array(); // array of widget instances
    $dashCustomWidgets = array(); // list of templates

    if ($panthera->varCache)
    {
        if ($panthera->varCache->exists('dash.widgets'))
        {
            $widgets = $panthera -> varCache -> get('dash.widgets');
            $panthera -> logging -> output('Getting list of widgets from varCache', 'dash');
        }
    }

    if ($widgets == False)
    {
        // list of widgets in lib and content
        $widgetsDir = array();
        if (is_dir(PANTHERA_DIR. '/modules/dash/'))
            $widgetsDir = @scandir(PANTHERA_DIR. '/modules/dash/');

        $widgetsContentDir = array();
        if (is_dir(SITE_DIR. '/content/modules/dash/'))
            $widgetsContentDir = @scandir(SITE_DIR. '/content/modules/dash/');

        $widgets = array_merge($widgetsDir, $widgetsContentDir);
        unset($widgets[0]);
        unset($widgets[1]);
        
        if ($panthera -> varCache)
        {
            $panthera -> varCache -> set('dash.widgets', $widgets, 120);
            $panthera -> logging -> output('Saving widgets list to varCache', 'dash');
        }
    }
    
    // add widgets from lib and content directories to the list
    foreach ($widgets as $widget)
    {
        $widget = substr($widget, 0, strlen($widget)-11);
        
        if (!array_key_exists($widget, $settings))
            $settings[$widget] = False;
    }
    
    if (!isset($settings['gallery']))
        $settings['gallery'] = False;
        
    if (!isset($settings['lastLogged']))
        $settings['lastLogged'] = False;

    $panthera -> template -> push ('dashAvaliableWidgets', $settings);

    // recent gallery items
    if ($settings['gallery'] === True)
    {
        $panthera -> importModule('gallery');
        $panthera -> template -> push ('galleryItems', gallery::getRecentPicture('', 9));
    }

    // last logged in users
    if ($settings['lastLogged'] === True)
    {
        $u = getUsers('', 10, 0, 'lastlogin', 'DESC');
        $users = array();

        foreach ($u as $key => $value)
        {
            if ($value->attributes->superuser)
                continue;
                
            $users[] = array('login' => $value->getName(), 'time' => date_calc_diff(strtotime($value->lastlogin), time()), 'avatar' => pantheraUrl($value->profile_picture), 'uid' => $value->id);
        }

        $panthera -> template -> push ('lastLogged', $users);
    }

    // load all enabled widgets
    foreach ($settings as $widget => $enabled)
    {
        if ($enabled == True)
        {
            $dir = getContentDir('/modules/dash/' .$widget. '.widget.php');

            if ($dir == False)
                continue;

            $widgetName = $widget. '_dashWidget';

            try {
                include_once $dir;

                if (!class_exists($widgetName))
                {
                    $panthera -> logging -> output('Class ' .$widgetName. ' does not exists in ' .$dir. ' file, skipping this widget', 'dash');
                    continue;
                }

                $enabledWidgets[$widget] = new $widgetName($panthera);
                $enabledWidgets[$widget] -> display();

                if (isset($enabledWidgets[$widget]->template))
                    $dashCustomWidgets[] = $enabledWidgets[$widget]->template;
                else
                    $dashCustomWidgets[] = 'dashWidget_' .$widget;

            } catch (Exception $e) {
                $panthera -> logging -> output ('Cannot display a widget, got an exception: ' .$e->getMessage(), 'dash');
            }
        }
    }
    
    $template -> push ('dashCustomWidgets', $dashCustomWidgets);
}

// menu
$template -> push ('dash_menu', $menu);
$template -> push ('dash_messages', $panthera -> get_filters('ajaxpages.dash.msg', array()));

$panthera -> template -> display ('dash.tpl');
pa_exit();
