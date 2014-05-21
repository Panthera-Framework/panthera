<?php
/**
  * Show list of ajax pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Show list of ajax pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  */

class ajaxpagesAjaxControllerCore extends pageController
{
    protected $permissions = array('admin.debug.ajaxpages' => array('Index of ajax pages', 'ajaxpages'));
    
    protected $uiTitlebar = array(
        'Index of ajax pages', 'ajaxpages',
    );
    
    protected $files = array(
    
    );
    
    protected $requirements = array(
        'filesystem', 'httplib', 'meta',
    );
    
    /**
     * List all controllers directories
     * 
     * @hook array ajaxpages.admin Directories listing
     * @return array
     */
    
    protected function scanDirectories()
    {
        return $this -> panthera -> get_filters('ajaxpages.admin', array_merge(
            filesystem::scandirDeeply(SITE_DIR. '/content/pages'),
            filesystem::scandirDeeply(SITE_DIR. '/content/ajaxpages/admin'),
            filesystem::scandirDeeply(PANTHERA_DIR. '/pages'),
            filesystem::scandirDeeply(PANTHERA_DIR. '/ajaxpages/admin')
        ), True);
    }
    
    /**
     * Update controllers informations
     * 
     * @return array
     */
    
    public function getControllersInfo($dontAjaxExit=False)
    {
        if ($this -> panthera -> cache and $_GET['action'] != 'forceResetCache')
        {
            if ($this -> panthera -> cache -> exists('ajaxpages.controllersInfo'))
                return $this -> panthera -> cache -> get('ajaxpages.controllersInfo');
                
            if ($_GET['action'] != 'forceResetCache')
                return array();
        }
        
        $array = array();
        
        foreach ($this -> files as $file)
        {
            if (strpos($file, '.Controller.php') !== false)
            {
                $controllerName = str_replace('.Controller.php', '', basename($file));
                $path = str_replace(PANTHERA_DIR, '', 
                        str_replace(SITE_DIR, '', 
                        str_replace($controllerName, '', 
                        str_replace('.Controller.php', '', $file))));
                        
                $path = trim($path, '/');
                
                if ($this -> panthera -> varCache)
                {
                    $this -> panthera -> varCache -> set('pa-login.system.loginkey', array(
                        'key' => generateRandomString(128),
                        'userID' => $this -> panthera -> user -> id,
                    ), 120);
                    
                    $attributes = $this -> checkControllerSyntax($controllerName, $path);
                    
                    if (!$attributes)
                    {
                        $this -> panthera -> varCache -> remove('pa-login.system.loginkey');
                        
                        if ($dontAjaxExit)
                            ajax_exit(array(
                                'status' => 'failed',
                                'message' => slocalize('PHP syntax error detected in %s controller (%s file)', 'ajaxpages', $controllerName, $file),
                            ));
                    }
                    
                    // required to bypass database too many connections error
                    sleep(0.2);
                    
                    //print("Checking ".$controllerName."\n");
                    //$this -> panthera -> outputControl -> flush();
                }
                
                if ($attributes)
                {
                    unset($attributes['instance']);
                    $array[$file] = $attributes;
                }
            }
        }

        if ($this -> panthera -> varCache)
            $this -> panthera -> varCache -> remove('pa-login.system.loginkey');
        
        if ($this -> panthera -> cache)
            $this -> panthera -> cache -> set('ajaxpages.controllersInfo', $array, -1);
            
        meta::updateListsFromControllers($array);
        return $array;
    }

    /**
     * Reset cache and validate all controllers again
     * 
     * @return null
     */

    protected function forceResetCacheAction()
    {
        $_GET['forceResetCache'] = true;
        $this -> files = $this -> scanDirectories();
        
        if ($this -> getControllersInfo())
        {
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Unknown error'),
        ));
    }

    /**
     * Check controller syntax using http method (as many servers don't have access to shell we can test every controller by invoking a separate test via http request)
     * 
     * @param string $controllerName Controller name
     * @param string $path Path
     */

    protected function checkControllerSyntax($controllerName, $path)
    {
        $http = new httplib;
        $key = $this -> panthera -> varCache -> get('pa-login.system.loginkey');
        
        $data = $http -> get(pantheraUrl('{$PANTHERA_URL}/_ajax.php?_bypass_x_requested_with&_system_loginkey=' .$key['key']. '&display=ajaxpages&cat=admin&action=checkControllerSyntax&controllerName=' .$controllerName. '&path=' .$path));
        $json = json_decode($data, true);
        $http -> close();
        
        if (!$json)
            return false;
        
        return $json['data'];
    }
    
    /**
     * Check controller syntax action
     * 
     * @input string $_GET['controllerName'] Controller name
     * @input string $_GET['path'] Path
     * 
     * @return null
     */
    
    protected function checkControllerSyntaxAction()
    {
        ajax_exit(array(
            'status' => 'success',
            'data' => pageController::getControllerAttributes($_GET['controllerName'], $_GET['path']),
        ));
    }
    
    /**
     * Displays results (everything is here)
     * 
     * @hook array ajaxpages_list.raw Raw, unfiltered list of ajax pages
     * @hook array ajaxpages_list List of ajax pages
     * @return string
     */
    
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('ajaxpages');
        $this -> panthera -> importModule('filesystem');
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'left');
        $this -> dispatchAction();
        
        // scan both lib and content
        $this -> files = $this -> scanDirectories();
        $controllersInfo = $this -> getControllersInfo();
        
        // list of pages
        $this -> pages = array();
        
        foreach ($this -> files as $file)
        {
            $pathinfo = pathinfo($file);
        
            if (strtolower($pathinfo['extension']) != 'php')
                continue;
        
            if (!is_file($file))
                continue;
        
            $location = 'unknown';
            
            // check if it's from /content or /lib
            if (strpos($file, SITE_DIR) !== False)
            {
                $location = 'content';
                $name = str_replace(SITE_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
            } elseif (strpos($file, PANTHERA_DIR) !== False) {
                $location = 'lib';
                $name = str_replace(PANTHERA_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
            }
            
            $directory = str_replace('/ajaxpages/', '', dirname($name));
            $name = str_ireplace('.php', '', basename($name));
            $permissions = '';
            $title = '';
            $permissionsWarning = False;
            
            // permissions
            if (isset($controllersInfo[$file]))
            {
                if ($controllersInfo[$file]['permissions'])
                {
                    if (is_array($controllersInfo[$file]['permissions']))
                    {
                        $permissions = '';
                        
                        foreach ($controllersInfo[$file]['permissions'] as $perm => $val)
                        {
                            if (is_int($perm))
                                $perm = $val;
                            
                            $permissions .= $perm. ', ';
                        }
                        
                        $permissions = trim($permissions, ', ');
                        
                    } else {
                        $permissions = $controllersInfo[$file]['permissions'];
                    }
                }
                
                // if controller does not implement any permissions check
                if (!$controllersInfo[$file]['permissions'] and !$controllersInfo[$file]['actionPermissions'])
                {
                    $permissionsWarning = True;
                }
            }
            
            // title
            if (isset($controllersInfo[$file]['uiTitlebar']))
            {
                if (is_array($controllersInfo[$file]['uiTitlebar']))
                    $title = localize($controllersInfo[$file]['uiTitlebar'][0], $controllersInfo[$file]['uiTitlebar'][1]);
                else
                    $title = localize($controllersInfo[$file]['uiTitlebar']);                
            }
        
            $this -> pages[] = array(
                'location' => $location,
                'directory' => $directory,
                'modtime' => date($this -> panthera -> dateFormat, filemtime($file)),
                'name' => str_replace('.Controller', '', $name),
                'path' => $file,
                'link' => '?display=' .str_replace('.Controller', '', $name),
                'objective' => (strpos($name, '.Controller') !== False),
                'permissions' => $permissions,
                'title' => $title,
                'permissionsWarning' => $permissionsWarning,
            );
            
            if (isset($controllersInfo[$file]))
            {
                foreach ($controllersInfo[$file]['__methods'] as $method => $class)
                {
                    if (strpos($method, 'Action') === false)
                        continue;
                    
                    $method = str_replace('Action', '', $method);
                    
                    if ($method == 'dispatch')
                        continue;
                    
                    // global permissions
                    if ($controllersInfo[$file]['permissions'])
                    {
                        if (is_array($controllersInfo[$file]['permissions']))
                        {
                            $permissions = '';
                            
                            foreach ($controllersInfo[$file]['permissions'] as $perm => $val)
                            {
                                if (is_int($perm))
                                    $perm = $val;
                                
                                $permissions .= $perm. ', ';
                            }
                            
                            $permissions = trim($permissions, ', ');
                        } else
                            $permissions = $controllersInfo[$file]['permissions'];
                            
                        $warning = false;
                    } else {
                    
                        // permissions per action
                        $permissions = localize('None', 'ajaxpages');
                        $warning = true;
                        
                        if (isset($controllersInfo[$file]['actionPermissions'][$method]))
                        {
                            if (is_array($controllersInfo[$file]['actionPermissions'][$method]))
                            {
                                $permissions = '';
                                
                                foreach ($controllersInfo[$file]['actionPermissions'][$method] as $k => $v)
                                {
                                    if (is_int($k))
                                        $k = $v;
                                    
                                    $permissions .= $k. ', ';
                                }
                                
                                $permissions = rtrim($permissions, ', ');
                                $warning = false;
                            } elseif (is_string($controllersInfo[$file]['actionPermissions'][$method])) {
                                $permissions = $controllersInfo[$file]['actionPermissions'][$method];
                                $warning = false;
                            } elseif (is_int($controllersInfo[$file]['actionPermissions'][$method])) {
                                $permissions = localize('Permissions checked inline', 'ajaxpages');
                                $warning = false;
                            }
                        }
                    }

                    $title = '';
                    
                    if (isset($controllersInfo[$file]['actionuiTitlebar'][$method]))
                    {
                        if (is_array($controllersInfo[$file]['actionuiTitlebar'][$method]))
                            $title = localize($controllersInfo[$file]['actionuiTitlebar'][$method][0], $controllersInfo[$file]['actionuiTitlebar'][$method][1]);
                        else
                            $title = localize($controllersInfo[$file]['actionuiTitlebar'][$method]);
                    }

                    $this -> pages[] = array(
                        'info' => $method,
                        'permissions' => $permissions,
                        'permissionsWarning' => $warning,
                        'title' => $title,
                    );
                }   
            }
            
            /*$this -> pages[] = array(
                'info' => '-> displayCategories',
                'rights' => 'can_view_article_categories',
            );*/
        }

        $this -> pages = $this -> panthera -> get_filters('ajaxpages_list.raw', $this -> pages, True);

        // ui.Searchbar integration
        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);
        
        if ($sBar -> getQuery())
        {
            $this -> pages = $sBar -> filterData($this -> pages, $sBar -> getQuery());
        }
        
        $this -> pages = $this -> panthera -> get_filters('ajaxpages_list', $this -> pages, True);
        $this -> panthera -> template -> push('pages', $this -> pages);
        
        return $this -> panthera -> template -> compile('ajaxpages.tpl');
    }   
    
}
