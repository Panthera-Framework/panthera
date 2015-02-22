<?php
/**
 * Dash ajax page
 *
 * @package Panthera\core\adminUI\dash
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Dash ajax page controller
 * Dash is a simple screen that contains menus and widgets after successful user login
 *
 * @package Panthera\core\adminUI\dash
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class dashAjaxControllerSystem extends pageController
{
    protected $uiTitlebar = array(
        'Everything is here', 'dash'
    );

    protected $actionPermissions = array(
        'remove' => array('admin.dash.managewidgets' => array('Can manage dash widgets', 'dash')),
        'add' => array('admin.dash.managewidgets' => array('Can manage dash widgets', 'dash')),
    );

    /**
     * Remove widget from dashboard
     *
     * @param string $_GET['widget'] Widget name
     * @author Damian Kęska
     */

    protected function removeAction()
    {
        $widgets = $this -> panthera -> config -> getKey('dash.widgets');

        // disable widget
        if(isset($widgets[$_GET['widget']]))
        {
            $widgets[$_GET['widget']] = False;
        }

        $this -> panthera -> config -> setKey('dash.widgets', $widgets, 'array', 'dash');
        $this -> panthera -> template -> push('widgetsUnlocked', 1);
    }

    /**
     * Add a widget from /modules/dash/ directory or builtin (gallery or lastLogged)
     *
     * @param string $_GET['widget'] Widget name
     * @author Damian Kęska
     */

    protected function addAction()
    {
        $widget = addslashes(str_replace('/', '', $_GET['widget']));

        if (getContentDir('modules/dash/' .$widget. '.widget.php'))
        {
            $widgets = $this -> panthera -> config -> getKey('dash.widgets');
            $widgets[$widget] = True;
            $this -> panthera -> config -> setKey('dash.widgets', $widgets, 'array', 'dash');
        }

        $this -> panthera -> template -> push('widgetsUnlocked', 1);
    }

    /**
     * Main action
     *
     * @feature ajaxpages.dash.menu.before (&array)$menuDB Modify menu database
     * @fetaure ajaxpages.dash.menu (array)$menuDB (string)$menuName Modify menu database after pagination etc.
     * @return null
     */

    protected function main()
    {
        $this -> panthera -> logging -> startTimer();

        $menuDB = array();
        $this -> getDefaultIcons($menuDB);
        $this -> getFeatureRef('ajaxpages.dash.menu.before', $menuDB);

        // hide items makred as hidden
        foreach ($menuDB as $key => &$item)
        {
            if ($item['hidden'])
            {
                unset($menuDB[$key]);
                unset($item);
            }
        }

        $maxItems = $this -> panthera -> config -> getKey('dash.maxItems', 16, 'int', 'dash');
        $menu = array();

        // support for paging
        switch ($_GET['menu'])
        {
            case 'more':
                $menu = $menuDB;
                $menu[] = array(
                    'link' => '?display=dash&cat=admin',
                    'name' => localize('Less', 'dash'),
                    'icon' => '{$PANTHERA_URL}/images/admin/tango-icon-theme/Go-previous.svg',
                    'linkType' => 'ajax',
                );

                foreach ($menu as &$item)
                {
                    if (is_array($item['name']))
                        $item['name'] = $this -> panthera -> locale -> localizeFromArray($item['name']);
                }
            break;

            case '':
                $_GET['menu'] = 'main';
                $num=0;

                foreach ($menuDB as $key => &$item)
                {
                    $num++;

                    if ($num == $maxItems)
                        break;

                    if (is_array($item['name']))
                        $item['name'] = $this -> panthera -> locale -> localizeFromArray($item['name']);

                    $menu[$key] = $menuDB[$key];
                }

                if ($num == $maxItems)
                {
                    $menu[] = array(
                        'link' => '?display=dash&cat=admin&menu=more',
                        'name' => localize('More', 'dash'),
                        'icon' => '{$PANTHERA_URL}/images/admin/tango-icon-theme/Go-next.svg',
                        'linkType' => 'ajax',
                    );
                }

                // main menu, there are predefined variables
                $this -> panthera -> template -> push('showWidgets', True);
            break;
        }

        // plugins and modifications support
        list($menu, $category) = $this -> getFeature('ajaxpages.dash.menu', array($menu, $_GET['menu']));

        // menu
        $this -> panthera -> template -> push ('dash_menu', $menu);
        $this -> panthera -> template -> push ('dash_messages', $this -> panthera -> get_filters('ajaxpages.dash.msg', array()));

        $this -> displayWidgets();

        return $this -> panthera -> template -> compile('dash.tpl');
    }

    /**
     * Display dash widgets
     *
     * @return null
     */

    protected function displayWidgets()
    {
        // just add permission to the list
        $this->checkPermissions(array('admin.accesspanel' => array('Can access admin panel')), true);

        $manageWidgets = $this->checkPermissions(array(
            'admin.dash.managewidgets' => array('Can manage dash widgets', 'dash')
        ), true);

        $this -> panthera -> template -> push('showWidgets', $manageWidgets);

        if ($this -> panthera -> config -> getKey('dash.enableWidgets', 1, 'bool', 'dash') and $manageWidgets)
        {
            $settings = $this -> panthera -> config -> getKey('dash.widgets', array('gallery' => True, 'lastLogged' => True), 'array', 'dash');
            $widgets = False;
            $enabledWidgets = array(); // array of widget instances
            $dashCustomWidgets = array(); // list of templates

            // get widgets list from varCache
            if ($this -> panthera -> varCache)
            {
                if ($this -> panthera -> varCache -> exists('dash.widgets'))
                {
                    $this -> panthera -> logging -> startTimer();
                    $widgets = $this -> panthera -> varCache -> get('dash.widgets');
                    $this -> panthera -> logging -> output('Getting list of widgets from varCache', 'dash');
                }
            }

            // save widgets list to varCache
            if ($widgets === False)
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

                if ($this -> panthera -> varCache)
                {
                    $this -> panthera -> logging -> startTimer();
                    $this -> panthera -> varCache -> set('dash.widgets', $widgets, 120);
                    $this -> panthera -> logging -> output('Saving widgets list to varCache', 'dash');
                }
            }

            // add widgets from lib and content directories to the list
            foreach ($widgets as $widget)
            {
                $widget = substr($widget, 0, strlen($widget)-11);

                if (!isset($settings[$widget]))
                    $settings[$widget] = False;
            }

            $this -> getFeatureRef('ajaxpages.admin.avaliable.widgets', $settings);
            $this -> panthera -> template -> push ('dashAvaliableWidgets', $settings);

            // execute all widgets code
            foreach ($settings as $widget => $enabled)
            {
                if ($enabled)
                {
                    $dir = getContentDir('/modules/dash/' .$widget. '.widget.php');

                    if (!$dir)
                        continue;

                    $widgetName = $widget. '_dashWidget';

                    try {
                        include_once $dir;

                        if (!class_exists($widgetName))
                        {
                            $this -> panthera -> logging -> output('Class ' .$widgetName. ' does not exists in ' .$dir. ' file, skipping this widget', 'dash');
                            continue;
                        }

                        $enabledWidgets[$widget] = new $widgetName($panthera);
                        $dashCustomWidgets[] = $enabledWidgets[$widget] -> display();

                    } catch (Exception $e) {
                        $this -> panthera -> logging -> output ('Cannot display a widget, got an exception: ' .$e->getMessage(), 'dash');
                    }
                }
            }

            $this -> getFeatureRef('ajaxpages.admin.dashwidgets', $dashCustomWidgets);
            $this -> panthera -> template -> push ('dashCustomWidgets', $dashCustomWidgets);
        }
    }

    /**
     * Main function
     *
     * @return null
     */

    public function display()
    {
        $this -> panthera -> config -> loadSection('dash');
        $this -> panthera -> locale -> loadDomain('dash');
        $this -> panthera -> template -> push('widgetsUnlocked', 0);

        $this -> dispatchAction();
        return $this -> main();
    }

    /**
     * Get list of default icons
     *
     * @param array &$defaults Input array
     * @return array
     */

    protected function getDefaultIcons(&$defaults)
    {
        $defaults['frontpage'] = array('link' => '{$PANTHERA_URL}', 'name' => array('Front page', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/home.png');
        $defaults['settings'] = array('link' => '?display=settings&cat=admin', 'name' => array('Settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/settings.png' , 'linkType' => 'ajax');
        //$defaults['debug'] = array('link' => '?display=debug&cat=admin', 'name' => array('Debugging center'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/developement.png', 'linkType' => 'ajax');
        //$defaults['mailing'] = array('link' => '?display=mailing&cat=admin', 'name' => array('Mailing', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png', 'linkType' => 'ajax');
        $defaults['newsletter'] = array('link' => '?display=newsletter&cat=admin', 'name' => array('Newsletter'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'linkType' => 'ajax');
        $defaults['gallery'] = array('link' => '?display=gallery&cat=admin', 'name' => array('Gallery'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/gallery.png', 'linkType' => 'ajax');
        $defaults['upload'] = array('link' => 'panthera.popup.toggle(\'_ajax.php?display=upload&cat=admin&popup=true\');', 'name' => localize('Uploads', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/uploads.png', 'linkType' => 'onclick');
        $defaults['contact'] = array('link' => '?display=contact&cat=admin', 'name' => array('Contact'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/contact.png', 'linkType' => 'ajax');
        $defaults['custom'] = array('link' => '?display=custom&cat=admin', 'name' => array('Custom pages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'linkType' => 'ajax');
        $defaults['messages'] = array('link' => '?display=messages&cat=admin', 'name' => array('Quick messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/messages.png', 'linkType' => 'ajax');
        //$defaults['pmessages'] = array('link' => '?display=privatemessages&cat=admin', 'name' => array('Private messages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', 'linkType' => 'ajax');
        $defaults['users'] = array('link' => '?display=users&cat=admin', 'name' => array('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');

        return $defaults;
    }
}