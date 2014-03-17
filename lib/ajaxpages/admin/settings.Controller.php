<?php
/**
 * Settings ajax page
 *
 * @package Panthera\core\ajaxpages
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU Lesser General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;
 
/**
 * Settings ajax page controller
 *
 * @package Panthera\core\ajaxpages
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU Lesser General Public License 3, see license.txt
 */

class settingsAjaxControllerSystem extends pageController
{
    protected $actionPermissions = array(
        'system_info' => 'can_see_system_info',
    );
    
    protected $defaultAction = 'main';
    
    protected $uiTitlebar = array(
        'Settings', 'settings'
    );
    
    /**
     * Dummy function to be forked
     * 
     * @return array Array with list of all informations
     */
    
    public function getSystemInfoOptions()
    {
        $options = array (
            'template' => $this -> panthera -> config -> getKey('template'),
            'timezone' => $this -> panthera -> config -> getKey('timezone'),
            'System Time' => date($this -> panthera -> dateFormat),
            'url' => $this -> panthera->config->getKey('url'),
            'ajax_url' => $this -> panthera->config->getKey('ajax_url'),
            '__FILE__' => __FILE__,
            'PANTHERA_DIR' => PANTHERA_DIR,
            'SITE_DIR' => SITE_DIR,
            'Panthera Version' => PANTHERA_VERSION,
            'Panthera debugger active' => $yn[intval($this -> panthera -> config -> getKey('debug'))],
            'Session lifetime' => $this -> panthera->config->getKey('session_lifetime', '3600', 'int'),
            'Session browser check' => $yn[$this -> panthera->config->getKey('session_useragent')],
            'Cookie encryption' => $yn[$this -> panthera->config->getKey('cookie_encrypt')],
            'PHP' => phpversion(),
            'magic_quotes_gpc' => $yn[intval(ini_get('magic_quotes_gpc'))],
            'register_globals' => $yn[intval(ini_get('register_globals'))],
            'session.save_handler' => ini_get('session.save_handler'),
            'memory_limit' => ini_get('memory_limit'),
            'display_errors' => $yn[ini_get('display_errors')],
            'post_max_size' => ini_get('post_max_size'),
            'PDO Drivers' => implode(', ', PDO::getAvailableDrivers()),
            'Template engine' => $this -> panthera->template->engine,
            'Server software' => $_SERVER['SERVER_SOFTWARE'],
            'System' => @php_uname(),
        );
        
         /** PHP APC **/

        $options['apc.cache_by_default'] = $yn[intval(ini_get('apc.cache_by_default'))];
        $options['apc.enabled'] = $yn[intval(ini_get('apc.enabled'))];

        /** MEMCACHED **/

        if (class_exists('Memcached'))
            $options['memcached'] = localize('Avaliable');

        /** Xcache **/

        if (extension_loaded('xcache'))
            $options['xcache'] = localize('Avaliable');

        /** Panthera cache system **/

        $options['varCache'] = $this -> panthera -> config -> getKey('varcache_type', 'db', 'string');
        $options['cache'] = $this -> panthera -> config -> getKey('cache_type', 'db', 'string');
        
        return $options;
    }

    /**
     * Display system informations
     * 
     * @return null
     */
    
    public function system_infoAction()
    {
        $this -> panthera -> template -> push(array(
            'action' => '',
            'user_uid' => '',
            'locales' => $this -> panthera -> locale -> getLocales(),
            'locale' => $this -> panthera -> locale -> getActive(),
        ));
        
        $yn = array(
            0 => localize('False'), 
            1 => localize('True')
        );
        
        $options = $this -> getSystemInfoOptions();

        /** Constants **/
        $const = get_defined_constants(true);
        $this -> panthera -> template -> push('const', $const['user']);
        
        $options = $this -> panthera -> get_filters('admin.ajaxpages.settings.options', $options);
        
        if (!defined('DISABLE_BROWSER_DETECTION'))
            $this -> panthera -> template -> push ('clientInfo', (array)$this -> panthera -> session -> get('clientInfo'));
            
        $this -> panthera -> template -> push('constants', $const['user']);
        $this -> panthera -> template -> push('settings_list', $options);
        $this -> panthera -> template -> push('acl_list', $this -> panthera -> user->acl->listAll());
        $this -> panthera -> template ->  push('action', 'system_info');
        
        $titlebar = new uiTitlebar(localize('Panel with main settings and informations about Panthera', 'settings'));
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/settings.png', 'left');
        
        $this -> panthera -> template -> display('settings_systeminfo.tpl');
        pa_exit();
    }

    /**
     * Get items list
     * 
     * @return array List of settings icons
     */

    public function getItemsList($defaults)
    {
        return $this -> panthera -> config -> getKey('ajaxpages.settings.items', $defaults, 'array', 'settings');
    }

    /**
     * void Main()
     * 
     * @hook array $defaults List of all default settings
     * @return string
     */

    public function mainAction()
    {
        $defaults = array();
        $defaults['system'] = array();
        $defaults = $this -> populateSystemDefaults($defaults);
        
        $defaults['content'] = array();
        $defaults = $this -> populateContentDefaults($defaults);
        
        // plugins
        $defaults = $this -> panthera -> get_filters('ajaxpages.settings.icons', $defaults);
        
        // searchbar
        $sBar = new uiSearchbar('uiTop');
        //$sBar -> setMethod('POST');
        $sBar -> setAddress('?display=settings&cat=admin');
        $sBar -> navigate(True);
        $sBar = $this -> panthera -> get_filters('ajaxpages.settings.searchbar', $sBar);
        
        $this -> panthera -> logging -> startTimer();
        $defaultsSum = hash('md4', serialize($defaults));
        
        // settings main menu
        $listDB = $this -> getItemsList($defaults);
        
        if ($this -> panthera -> config -> getKey('settings.items.checksum') != $defaultsSum)
        {
            // @feature: not overwriting entries that was marked as "edited"    
            foreach ($defaults as $sectionName => $section)
            {
                foreach ($section as $key => $item)
                {
                    if ($listDB[$sectionName][$key]['edited'])
                    {
                        continue;
                    }
                
                    $listDB[$sectionName][$key] = $item;
                }
            }
            
            $this -> panthera -> config -> setKey('settings.items', $defaults, 'array', 'settings');
            $this -> panthera -> config -> setKey('settings.items.checksum', $defaultsSum, 'string', 'settings');
            $this -> panthera -> logging -> output ('Updated default settings items', 'settings');
        }
        
        foreach ($listDB as $sectionName => $section)
        {
            foreach ($section as $key => $value)
            {
                if (isset($value['hidden']))
                {
                    if ($value['hidden'])
                    {
                        unset($listDB[$sectionName][$key]);
                    }
                }
            }
        }
        
        $listDB = $this -> panthera -> get_filters('ajaxpages.settings.items', $listDB);
        $list = $listDB;
        
        if ($sBar->getQuery())
        {
            foreach ($list as $categoryName => $category)
            {
                $list[$categoryName] = $sBar->filterData($category, $sBar->getQuery());
            }
        }
        
        // check for permissions
        $list = $this -> permissionsCheck($list);
        
        $this -> panthera -> template -> push('items', $list);
        $this -> panthera -> template -> display('settings.tpl');
        pa_exit();
    }

    /**
     * Permissions check for every item on the list
     * 
     * @param array $list Input list
     * @return array Filtered list
     */

    protected function permissionsCheck($list)
    {
        if ($this -> panthera -> varCache)
        {
            if ($this -> panthera -> varCache -> exists('ajaxpages.settings.meta'))
            {
                $permissions = $this -> panthera -> varCache -> get('ajaxpages.settings.meta');
            } else {
                foreach ($list as $sectionName => $values)
                {
                    foreach ($values as $key => $value)
                    {
                        parse_str(substr($value['link'], 1, strlen($value['link'])), $params);
                        
                        if (!isset($params['display']))
                            continue;
                        
                        $test = pageController::getControllerAttributes($params['display'], 'ajaxpages/admin');
                        
                        if ($test['defaultAction'])
                        {
                            if (isset($test['actionPermissions'][$test['defaultAction']]))
                                $test['permissions'] = $test['actionPermissions'][$test['defaultAction']];
                        }
                        
                        $permissions[] = array($sectionName, $key, $test['permissions']);
                    }
                }
                
                $this -> panthera -> varCache -> set('ajaxpages.settings.meta', $permissions, 3600);
            }

            foreach ($permissions as $controller => $permission)
            {
                if (!isset($permission[2]))
                    continue;
                
                if (!$this->checkPermissions($permission[2], True))
                {
                    unset($list[$permission[0]][$permission[1]]);
                }
            }
        }
        
        return $list;
    }

    /**
     * Display data
     * 
     * @return null
     */
    
    public function display()
    {
        $this -> dispatchAction();
    }
    
    /**
     * Add system icons
     * 
     * @param array $defaults Input array
     * @return array Output array
     */

    protected function populateSystemDefaults($defaults)
    {
        $defaults['system']['database'] = array(
            'link' => '?display=database&cat=admin', 
            'name' => localize('Database management', 'settings'), 
            'description' => localize('Monitor connection status, create backups', 'settings'), 
            'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png' , 
            'linkType' => 'ajax'
        );
        
        $defaults['system']['cache'] = array(
            'link' => '?display=cache&cat=admin', 
            'name' => localize('Cache management', 'settings'),
            'description' => localize('Monitor and manage cache settings', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/cache.png' , 
            'linkType' => 'ajax'
        );
        
        $defaults['system']['leopard'] = array(
            'link' => '?display=leopard&cat=admin', 
            'name' => localize('Package management', 'settings'),
            'description' => localize('Install or remove Panthera packages', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/package.png' , 
            'linkType' => 'ajax'
        );
        
        $defaults['system']['conftool'] = array(
            'link' => '?display=conftool&cat=admin', 
            'name' => localize('Configuration editor', 'dash'), 
            'icon' => '{$PANTHERA_URL}/images/admin/menu/config.png', 
            'linkType' => 'ajax'
        );
        
        $defaults['system']['locales'] = array(
            'link' => '?display=locales&cat=admin',
            'name' => localize('Language settings', 'dash'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/locales.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['plugins'] = array(
            'link' => '?display=plugins&cat=admin',
            'name' => ucfirst(localize('plugins', 'dash')),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-preferences-plugin-icon.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['templates'] = array(
            'link' => '?display=templates&cat=admin',
            'name' => ucfirst(localize('templates', 'dash')),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/Icon-template.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['session'] = array(
            'link' => '?display=settings.session&cat=admin',
            'name' => ucfirst(localize('session', 'settings')),
            'description' => localize('Session, cookies and browser security settings', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/session-icon.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['mce'] = array(
            'link' => '?display=settings.mce&cat=admin',
            'name' => ucfirst(localize('mce settings', 'settings')),
            'description' => localize('Text editor settings', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/mce.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['dash'] = array(
            'link' => '?display=settings.dash&cat=admin',
            'name' => localize('Dashboard', 'settings'),
            'description' => localize('Configure Admin Panel main screen', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/dashboard.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['pager'] = array(
            'link' => '?display=settings.pager&cat=admin',
            'name' => localize('Pager settings', 'settings'),
            'description' => localize('Setup all pagers used in Panthera Framework', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/pager.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['crontab'] = array(
            'link' => '?display=crontab&cat=admin',
            'name' => localize('Crontab', 'settings'),
            'description' => localize('Scheduled jobs management - crontab', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/crontab.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['upload'] = array(
            'link' => '?display=upload&cat=admin',
            'name' => localize('Upload management', 'settings'),
            'description' => localize('Manage upload categories, files', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/uploads.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['register'] = array(
            'link' => '?display=settings.register&cat=admin',
            'name' => localize('User registration', 'settings'),
            'description' => localize('New users registration management', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/register.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['login'] = array(
            'link' => '?display=settings.pa-login&cat=admin',
            'name' => localize('Login screen settings', 'settings'),
            'description' => '',
            'icon' => '{$PANTHERA_URL}/images/admin/menu/login-settings.png',
            'linkType' => 'ajax'
        );
        
        $defaults['system']['facebook'] = array(
            'link' => '?display=settings.facebook&cat=admin',
            'name' => localize('Facebook integration', 'settings'),
            'description' => '',
            'icon' => '{$PANTHERA_URL}/images/admin/menu/facebook.png',
            'linkType' => 'ajax'
        );

        return $defaults;
    }

    /**
     * Add content management related icons
     * 
     * @param array $defaults Input array
     * @return array Output array
     */

    protected function populateContentDefaults($defaults)
    {
        $defaults['content']['users'] = array(
            'link' => '?display=users&cat=admin',
            'name' => ucfirst(localize('users', 'settings')),
            'description' => localize('Manage users', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['mailing'] = array(
            'link' => '?display=mailing&cat=admin',
            'name' => localize('Mailing', 'dash'),
            'description' => localize('Send e-mails, manage it\'s configuration', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['newsletter'] = array(
            'link' => '?display=settings.newsletter&cat=admin',
            'name' => localize('Newsletter settings', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/newsletter.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['menuedit'] = array(
            'link' => '?display=menuedit&cat=admin',
            'name' => localize('Menu editor', 'dash'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['langtool'] = array(
            'link' => '?display=langtool&cat=admin',
            'name' => ucfirst(localize('translates', 'dash')),
            'description' => localize('Manage system translations', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/langtool.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['passwordrecovery'] = array(
            'link' => '?display=settings.passwordrecovery&cat=admin',
            'name' => ucfirst(localize('password recovery', 'settings')),
            'description' => localize('Default mail title, content, password length', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/password-recovery.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['sitesettings'] = array(
            'link' => '?display=settings.site&cat=admin',
            'name' => localize('Site configuration', 'settings'),
            'description' => localize('Website URL address, title', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/site-settings.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['custompages'] = array(
            'link' => '?display=settings.custompages&cat=admin',
            'name' => localize('Static pages configuration', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/custom-pages.png',
            'linkType' => 'ajax'
        );
        
        $defaults['content']['routing'] = array(
            'link' => '?display=routing&cat=admin',
            'name' => localize('SEO links management', 'routing'),
            'description' => localize('Front-end urls rewriting', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/routing.png',
            'linkType' => 'ajax'
        );
        
        return $defaults;
    }
}

