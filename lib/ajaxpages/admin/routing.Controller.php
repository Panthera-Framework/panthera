<?php
/**
 * Routing management
 * SEO links editor for Panthera Admin Panel
 *
 * @package Panthera\core\routing\admin
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
 */

class routingAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.datasheet',
        'admin/ui.pager',
    );
    
    protected $table = '';
    protected $uiTitlebar = array(
        'SEO links management', 'routing'
    );
    
    protected $permissions = 'admin.routing';
    
    protected $data = array();
    
    /**
     * Get routing cache (dummy method to be forked)
     * 
     * @hook admin.routing.cache array
     * @return array
     */
    
    public function getRoutingCache()
    {
        return $this -> panthera -> get_filters('admin.routing.cache', $this -> panthera -> routing -> getCache(), True);
    }
    
    /**
     * Prepare data to be inserted into a table
     * 
     * @param array $data Raw data from routing cache
     * @hook admin.routing.data array
     * @return array Prepared data to display in table
     */
    
    public function prepareData($data)
    {
        $dataTpl = array();
        
        if ($data['routes'])
        {
            foreach ($data['routes'] as $route)
            {
                $get = '';
                $post = '';
                
                if (isset($route[2]['GET']))
                    $get = getQueryString($route[2]['GET']);
                
                if (isset($route[2]['POST']))
                    $post = getQueryString($route[2]['POST']);
                
                $dataTpl[] = array(
                    'name' => $route[3],
                    'path' => $route[1],
                    'controller' => $route[2]['front'],
                    'methods' => implode(', ', explode('|', $route[0])),
                    'staticget' => $get,
                    'staticpost' => $post,
                    'redirect' => $route[2]['redirect'],
                    'code' => $route[2]['code'],
                );
            }
        }
        
        return $this -> panthera -> get_filters('admin.routing.data', $dataTpl, True);
    }
    
    /**
     * Add pager
     * 
     * @param array $data
     * @return array
     */
    
    public function page($data)
    {
        $uiPager = new uiPager('routingPager', count($data), 'adminRouting', 50);
        $uiPager -> setLinkTemplatesFromConfig('generic.tpl');
        
        $data = $uiPager -> limitArray($data);
        $this -> table -> adduiPager($uiPager);
        
        uiPager::applyToTemplate();
        
        return $data;
    }
    
    /**
     * Route deletion action
     * 
     * @param mixed $data
     * @return null
     */

    public function deleteAction($data='')
    {
        $id = $_POST['id'];
        
        $cache = $this->getRoutingCache();
        
        if (isset($cache['routes'][$id]))
        {
            $this -> panthera -> routing -> unmap($id);
            ajax_exit(array('status' => 'success'));
        }
                
        ajax_exit(array(
            'status' => 'failed', 
            'message' => localize('Cannot find specified route', 'routing')
        ));
    }
    
    /**
     * Edit/New action
     * 
     * @param mixed $data
     * @return null
     */
    
    public function editAction($data='')
    {
        $name = $_POST['name'];
        $controller = addslashes($_POST['controller']);
        $route = $_POST['path'];
        $method = $_POST['method'];
        $redirect = $_POST['redirect'];
        $redirectCode = intval($_POST['code']);
        
        $codes = array(
            '300', '301', '302', '303', '307',
        );
        
        parse_str($_POST['staticget'], $argsGET);
        parse_str($_POST['staticpost'], $argsPOST);
        
        $target = array(
            'GET' => $argsGET,
            'POST' => $argsPOST,
        );
        
        if (isset($_POST['oldid']))
        {
            $this -> panthera -> routing -> unmap(base64_decode($_POST['oldid']));
        }
        
        if (!$name)
        {
            ajax_exit(array(
                'status' => 'failed', 
                'message' => localize('Please input a name', 'routing'),
                'field' => 'name',
            ));
        }
        
        if (!$route)
        {
            ajax_exit(array(
                'status' => 'failed', 
                'message' => localize('Please input a route path', 'routing'),
                'field' => 'path',
            ));
        }
        
        if (!$redirectCode)
        {
            if (!is_file(SITE_DIR. '/' .$controller))
            {
                ajax_exit(array(
                    'status' => 'failed', 
                    'message' => localize('Front controller not found', 'routing'),
                    'field' => 'controller',
                ));
            }
            
            $target['front'] = $controller;
        } else {
            
            if (!in_array($redirectCode, $codes))
            {
                ajax_exit(array(
                    'status' => 'failed', 
                    'message' => localize('Invalid redirect code', 'routing'),
                    'field' => 'code',
                ));
            }
            
            $target['redirect'] = $redirect;
            $target['code'] = $redirectCode;
        }
        
        if ($method != 'GET' and $method != 'POST' and $method != 'GET|POST')
        {
            $method = 'GET|POST';
        }
        
        // map($method='GET|POST', $route, $target, $name)
        $this -> panthera -> routing -> map($method, $route, $target, $name);
        ajax_exit(array('status' => 'success'));
    }

    /**
     * Edit form action
     * 
     * @return null
     */

    public function editFormAction()
    {
        $id = base64_decode($_GET['id']);
        
        foreach ($this->data as $row)
        {
            if ($row[$this->table->idColumn] == $id)
            {
                $this -> panthera -> template -> push ('controllers', pageController::getFrontControllersList());
                $this -> panthera -> template -> push('itemRow', $row);
                $this -> panthera -> template -> push('tableID', $this->table->tableID);
                $this -> panthera -> template -> push('rowID', base64_encode($row[$this->table->idColumn]));
                $this -> panthera -> template -> display('routing.edit.tpl');
                pa_exit();
                break;
            }
        }
        
        pa_exit();
    }

    /**
     * Displays results (everything is here)
     * 
     * @hook admin.routing.table object uiDatasheet
     * @return string
     */
    
    public function display()
    {
        // create a table and put columns
        $this -> table = new uiDatasheet('routing');
        $this -> table -> headerAddColumn('name', localize('Route name', 'routing'));
        $this -> table -> headerAddColumn('path', localize('Input URL', 'routing'), '', False, False, False, True);
        $this -> table -> headerAddColumn('controller', localize('Controller', 'routing'));
        $this -> table -> headerAddColumn('methods', localize('HTTP methods', 'routing'), '', False, False, False, True);
        $this -> table -> headerAddColumn('staticget', localize('Static GET parameters', 'routing'));
        $this -> table -> headerAddColumn('staticpost', localize('Static POST parameters', 'routing'));
        $this -> table -> headerAddColumn('redirect', localize('Redirection', 'routing'), '', False, False, False, True);
        $this -> table -> headerAddColumn('code', localize('Redirection Code', 'routing'), '', False, False, False, True);
        $this -> table -> setIdColumn('name');
        $this -> table -> deleteButtons = True;
        $this -> table -> editButtons = True;
        $this -> data = $this -> prepareData($this->getRoutingCache());
        $this -> table -> addActionCallback('new', array($this, 'editAction'));
        $this -> table -> addActionCallback('edit', array($this, 'editAction'));
        $this -> table -> addActionCallback('remove', array($this, 'deleteAction'));
        $this -> table -> addActionCallback('editForm', array($this, 'editFormAction'));
        $this -> table -> dispatchAction();
        
        $sBar = new uiSearchbar('uiTop');
        $this -> table -> adduiSearchbar($sBar);
        $sBar -> navigate(True);
        
        if ($sBar -> getQuery())
        {
            $this -> data = $sBar -> filterData($this -> data, $sBar -> getQuery());
        }
        
        $filters = $sBar -> getFilters();
        
        if (isset($filters['order']) and isset($filters['direction']))
        {
            $this -> data = $sBar -> orderBy($this -> data, $filters['order'], $filters['direction']);
        }
        
        // hooking
        $this -> table = $this -> panthera -> get_filters('admin.routing.table', $this -> table, True);
        
        // append data to pager
        $this -> data = $this -> page($this -> data);
        
        // append data and draw
        $this -> table -> appendData($this -> data);
        
        $this -> panthera -> template -> push ('controllers', pageController::getFrontControllersList());
        $this -> panthera -> template -> push ('table', $this -> table -> draw());
        
        return $this -> panthera -> template -> compile('routing.tpl');
    }
}
