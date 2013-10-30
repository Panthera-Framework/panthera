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

// dash have it's own configuration section
$panthera -> config -> loadSection('dash');

$panthera -> locale -> loadDomain('dash');
$panthera -> template -> push('widgetsUnlocked', 0);

if (getUserRightAttribute($panthera->user, 'can_see_dash'))
{
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
}

// default list of pages displayed in dash
$defaults = array();
$defaults['frontpage'] = array('link' => '{$PANTHERA_URL}', 'name' => array('Front page', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/home.png');
$defaults['settings'] = array('link' => '?display=settings&cat=admin', 'name' => array('Settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/settings.png' , 'linkType' => 'ajax');
$defaults['debug'] = array('link' => '?display=debug&cat=admin', 'name' => array('Debugging center'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/developement.png', 'linkType' => 'ajax');
$defaults['mailing'] = array('link' => '?display=mailing&cat=admin', 'name' => array('Mailing', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png', 'linkType' => 'ajax');
$defaults['newsletter'] = array('link' => '?display=newsletter&cat=admin', 'name' => array('Newsletter'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'linkType' => 'ajax');
$defaults['gallery'] = array('link' => '?display=gallery&cat=admin', 'name' => array('Gallery'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/gallery.png', 'linkType' => 'ajax');
$defaults['upload'] = array('link' => 'createPopup(\'_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback\', 1300, 550);', 'name' => localize('Uploads', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/uploads.png', 'linkType' => 'onclick');
$defaults['contact'] = array('link' => '?display=contact&cat=admin', 'name' => array('Contact'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/contact.png', 'linkType' => 'ajax');
$defaults['custom'] = array('link' => '?display=custom&cat=admin', 'name' => array('Custom pages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'linkType' => 'ajax');
$defaults['messages'] = array('link' => '?display=messages&cat=admin', 'name' => array('Quick messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/messages.png', 'linkType' => 'ajax');
$defaults['pmessages'] = array('link' => '?display=privatemessages&cat=admin', 'name' => array('Private messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', 'linkType' => 'ajax');
$defaults['users'] = array('link' => '?display=users&cat=admin', 'name' => array('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');

$panthera -> logging -> startTimer();
$defaultsSum = hash('md4', serialize($defaults));
$menuDB = $panthera -> config -> getKey('dash.items', $defaults, 'array', 'dash');

if ($panthera -> config -> getKey('dash.items.checksum') != $defaultsSum)
{
    // @feature: not overwriting entries that was marked as "edited"    
    foreach ($defaults as $key => $item)
    {
        if ($menuDB[$key]['edited'])
        {
            continue;
        }
        
        $menuDB[$key] = $item;
    }
    
    $panthera -> config -> setKey('dash.items', $menuDB, 'array', 'dash');
    $panthera -> config -> setKey('dash.items.checksum', $defaultsSum, 'string', 'dash');
    $panthera -> logging -> output ('Updated default dash items', 'dash');
}

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
        $num=0;
        
        foreach ($menuDB as $key => $item)
        {
            // @feature: hiding items
            if ($item['hidden'])
            {
                continue;
            }
            
            $num++;
            
            if ($num == $maxItems)
            {
                break;
            }
            
            $item['name'] = $panthera -> locale -> localizeFromArray($item['name']);
            $menu[$key] = $item;
        }
        
        if ($num == $maxItems)
        {
            $menu[] = array('link' => '?display=dash&cat=admin&menu=more', 'name' => localize('More', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/tango-icon-theme/Go-next.svg' , 'linkType' => 'ajax');
        }
        
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
  
$panthera -> template -> push('showWidgets', getUserRightAttribute($panthera->user, 'can_see_dash'));

if ($panthera->config->getKey('dash.enableWidgets', 1, 'bool', 'dash') and getUserRightAttribute($panthera->user, 'can_see_dash'))
{
    $settings = $panthera -> config -> getKey('dash.widgets', array('gallery' => True, 'lastLogged' => True), 'array', 'dash');
    $widgets = False;
    $enabledWidgets = array(); // array of widget instances
    $dashCustomWidgets = array(); // list of templates

    if ($panthera->varCache)
    {
        if ($panthera->varCache->exists('dash.widgets'))
        {
            $panthera -> logging -> startTimer();
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
            $panthera -> logging -> startTimer();
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
        $panthera -> template -> push ('galleryItems', gallery::getRecentPicture('', 12));
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

// titlebar
$titlebar = new uiTitlebar(localize('Everything is here', 'dash'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/dashboard.png', 'left');

$panthera -> template -> display('dash.tpl');
pa_exit();
