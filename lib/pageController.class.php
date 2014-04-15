<?php
/**
  * Front controllers related functions
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Lesser General Public License 3, see license.txt
  */

define('_CONTROLLER_PERMISSION_INLINE_', 2);
  
/**
 * Abstract interface for front controllers
 * 
 * @package Panthera\core
 * @author Damian Kęska
 */


abstract class pageController extends pantheraClass {
    
    // list of required modules
    protected $requirements = array();
    
    // list of required configuration overlays
    protected $overlays = array();
    
    // information for dispatcher to look for this controller name (full class name)
    public static $searchFrontControllerName = '';
    
    // ui.Titlebar integration eg. array('SEO links management', 'routing')
    protected $uiTitlebar = array();
    protected $uiTitlebarObject = null;
    protected $actionuiTitlebar = array(); // diffirent titlebars per action eg. 'edit' => array('This is a test', 'domainname')
    
    // permissions list for action dispatcher
    protected $actionPermissions = array(
        /* 'delete' => array('admin'), */
        /* 'edit' => 'admin', */
        /* 'edit' => 'gallery_edit_{$id}', */
    );
    
    // list of variables to replace in permission names eg. array('id' => 1) and template example: gallery_{$id}
    protected $permissionsVariables = array();
    
    // default action to execute on page display (eg. main will execute $this->mainAction() by $this->dispatchAction())
    protected $defaultAction = null;
    
    // permissions required to view this page
    protected $permissions = '';
    
    // are we using uiNoAccess?
    protected $useuiNoAccess = null;
    
    // are we already added permissions icon to titlebar?
    protected $permissionsIconPresent = False;
    
    // list of all requested permissions
    protected $__permissions = array();
    
    /**
     * Initialize front controller
     * 
     * @return object
     */
    
    public function __construct ()
    {
        // run pantheraClass constructor to get Panthera Framework object
        parent::__construct();
        
        if ($this -> permissions)
            $this -> checkPermissions($this -> permissions);
        
        if ($this->requirements)
        {
            foreach ($this->requirements as $module)
            {
                $this -> panthera -> importModule($module);
                
                if (!$this -> panthera -> moduleImported($module))
                    throw new Exception('Cannot preimport required module "' .$module. '"', 234);
            }
        }
        
        if ($this -> overlays)
        {
            foreach ($this -> overlays as $overlay)
                $this -> panthera -> config -> loadOverlay($overlay);
        }
        
        // add uiTitlebar
        if ($this -> uiTitlebar)
        {
            if (isset($this -> uiTitlebar[1]))
                $name = localize($this->uiTitlebar[0], $this->uiTitlebar[1]);
            else
                $name = localize($this->uiTitlebar[0]);
            
            $this -> uiTitlebarObject = new uiTitlebar($name);
        }
        
        // enable ui.NoAccess for admin panel by default
        if (isset($_GET['cat']) and $this -> useuiNoAccess === null)
        {
            if($_GET['cat'] == 'admin')
                $this -> useuiNoAccess = true;
        }
    }

    /**
     * Check user permissions
     * Will use uiNoAccess if user permissions are insufficient
     * 
     * @param array|string $permissions List of permissions or just a single permission string
     * @param bool $dontCallNoAccess Don't call uiNoAccess on fail
     */

    public function checkPermissions($permissions, $dontCallNoAccess=False)
    {
        // admin can do everything
        if (getUserRightAttribute($this->panthera->user, True))
        {
            $this -> __addPermission($permissions);
            return True;
        }
        
        $valid = false;
        
        // single permission check
        if (is_string($permissions))
        {
            $valid = getUserRightAttribute($this->panthera->user, $permissions);
            
        // multiple permissions
        } elseif (is_array($permissions)) {
            
            foreach ($permissions as $permission => $title)
            {
                if (is_int($permission))
                    $permission = $title;
                
                if (getUserRightAttribute($this->panthera->user, $permission))
                    $valid = true;
            }   
        }
        
        if ($valid)
            return true;
        
        if (!$dontCallNoAccess)
        {
            // show information if permissions check failed
            $noAccess = new uiNoAccess;
            
            if (!is_array($permissions) and $permissions)
                $permissions = array($permissions);
            
            if ($permissions)
                $noAccess -> addMetas($permissions);
            
            $noAccess -> display();
        }
        
        return $valid;
    }

    /**
     * Add new variable to permissions variables
     * 
     * @param string|array $var Single variable or multiple variables
     * @param string|int $value Variable value
     * @author Damian Kęska
     * @return bool
     */
    
    protected function pushPermissionVariable($var, $value=null)
    {
        if (is_array($var))
            $this -> permissionsVariables = array_merge($this -> permissionsVariables, $var);
        else
            $this -> permissionsVariables[$var] = $value;
        
        return TRUE;
    }
    
    /**
     * Simple action dispatcher. Just type ?display=name in url to invoke $this->nameAction() method
     * 
     * @param string $action Optional action name to manually invoke
     * @return mixed
     */
    
    public function dispatchAction($action='')
    {
        if (!$action and isset($_GET['action']))
            $action = $_GET['action'];

        if (!$action and $this->defaultAction)
            $action = $this->defaultAction;
            
        if (!$action)
        {
            $this -> panthera -> logging -> output('No any action selected', get_class($this));
            return False;
        }
        
        $method = $action. 'Action';
        $this -> panthera -> logging -> output('Looking for "' .$method. '" method', get_class($this));
        
        if (isset($this->actionPermissions[$action]))
        {
            if ($this -> actionPermissions[$action] != _CONTROLLER_PERMISSION_INLINE_)
            {
                if (!is_array($this -> actionPermissions[$action]))
                    $this -> actionPermissions[$action] = array($this -> actionPermissions[$action]);
                
                if ($this->permissionsVariables)
                {
                    foreach ($this -> actionPermissions[$action] as $key => &$value)
                    {
                        foreach ($this->permissionsVariables as $variableName => $variableValue)
                            $value = str_replace('{$' .$variableName. '}', $variableValue, $value);
                    }
                }
                
                $this -> checkPermissions($this->actionPermissions[$action], $this->useuiNoAccess);
            }
        }
        
        // diffirent titlebar for every action
        if (isset($this->actionuiTitlebar[$action]))
        {
            // create action titlebar
            if (isset($this->actionuiTitlebar[$action][1]))
                $this -> uiTitlebarObject = new uiTitlebar(localize($this->actionuiTitlebar[$action][0], $this->actionuiTitlebar[$action][1]));
            else
                $this -> uiTitlebarObject = new uiTitlebar(localize($this->actionuiTitlebar[$action][0]));
        }
        
        if(method_exists($this, $method))
            return $this -> $method();
    }

    /**
     * Add permissions button on uiTitlebar
     * 
     * @return null
     */

    public function __uiTitlebarAddPermissions()
    {
        if (!$this->__permissions)
            return false;
        
        $perms = 'base64:' .base64_encode(serialize($this->__permissions));
        
        // get from template config (so we don't have to define paths and classes here)
        $config = (array)$this -> panthera -> template -> getFileConfig('uititlebar.tpl');
        $href = str_replace('{$query}', $perms, $config['permissions_href']);
        $onclick = str_replace('{$query}', $perms, $config['permissions_onclick']);
        
        $this -> getFeature('controller.permissions', $perms, get_class($this));
        
        if ($this -> uiTitlebarObject)
            $this -> uiTitlebarObject -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), 'right', $href, $onclick);
    }

    /**
     * Add permission to permissions list
     * 
     * @param string|array $permissions String permission or array of permissions with titles
     * @return bool
     */

    protected function __addPermission($permission)
    {
        if (is_string($permission))
        {
            $this -> __permissions[$permission] = $permission;
            
        } elseif (is_array($permission)) {
            
            foreach ($permission as $k => $v)
            {
                if (is_int($k))
                    $k = $v;
                
                if (is_array($v))
                    $v = localize($v[0], $v[1]);
                
                $this -> __permissions[$k] = $v;
            }
        } else
            return false;
        
        return true;
    }
    
    /**
     * Dummy function
     * 
     * @return null
     */
    
    public function display()
    {
        $this -> dispatchAction();
        return '';
    }
    
    /**
     * Use this function instead of display() to run controller
     * 
     * @return string
     */
    
    public function run()
    {
        if (checkUserPermissions(null, true))
            $this -> panthera -> add_option('template.display', array($this, '__uiTitlebarAddPermissions'), 1);
        
        $this -> getFeature('controller.run');
        return $this -> display();
    }
    
    /**
     * Represent object as string
     * 
     * @return string
     */
    
    public function __toString()
    {
        return $this->display();
    }
    
    /**
     * Get list of avaliable front controllers
     * 
     * @return array
     */
    
    public static function getFrontControllersList()
    {
        $files = scandir(SITE_DIR. '/');
        $list = array();
        
        foreach ($files as $file)
        {
            $pathinfo = pathinfo($file);
            
            if ($pathinfo['extension'] != 'php')
                continue;
            
            $list[] = $pathinfo['basename'];
        }
        
        return $list;
    }
    
    /**
     * List controller protected and public attributes with it's default values
     * 
     * @param string $name Controller name eg. "contact" (contact.Controller.php)
     * @param string $path Path to look for controller eg. pages or ajaxpages/admin
     * @return array|bool
     */
    
    public static function getControllerAttributes($name, $path='pages')
    {
        $file = getContentDir($path. '/' .$name. '.Controller.php');
        
        if (!$file)
            return False;
        
        include_once $file;
        
        $name = static::getControllerName($name);
        
        if (!$name and static::$searchFrontControllerName)
            $name = static::$searchFrontControllerName;
        
        // invalid controller class name        
        if (!$name)
            return False;
        
        $reflection = new ReflectionClass($name);
        $instance = $reflection -> newInstanceWithoutConstructor();
        
        $props = $reflection -> getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $propsReturn = array();
        
        foreach ($props as $property)
        {
            $property -> setAccessible(True);
            $propsReturn[$property->getName()] = $property->getValue($instance);
        }
        
        $propsReturn['__methods'] = array();
        
        foreach ($reflection -> getMethods() as $method)
        {
            $propsReturn['__methods'][$method->name] = $method->class;
        }
        
        unset($propsReturn['instance']);
        
        return $propsReturn;
    }
    
    /**
     * Lookup for controller class name
     * 
     * @param string $name Controller name
     * @return string|null
     */
    
    public static function getControllerName($name)
    {
        // replace all dots with "_"
        $name = str_replace('.', '_', str_replace('-', '_', $name));
        
        $custom = '____non_existent_controller___';
        
        if (static::$searchFrontControllerName)
            $custom = static::$searchFrontControllerName;
        
        $controllerNames = array(
            $custom,
            $name. 'Controller',
            $name. 'ControllerCore',
            $name. 'ControllerSystem',
            $name. 'AjaxController',
            $name. 'AjaxControllerCore',
            $name. 'AjaxControllerSystem',
        );
        
        foreach ($controllerNames as $className)
        {
            if (class_exists($className))
                return $className;
        }
    }
    
    /**
     * Create a new instance of controller (if exists)
     * 
     * @param string $name Controller name
     * @return object|null
     */
    
    public static function getController($name)
    {
        $name = static::getControllerName($name);
        
        if ($name)
            return new $name;
    }
    
    /**
     * Execute hooks and defined functions with name $featureName
     * Example: $featureName = 'custompages.add' will execute $this->custompages_addFeature($args, $additionalInfo) and $this->panthera->get_filters($featureName, $args, $additionalInfo)
     * 
     * @param string $featureName Hook and function name
     * @param mixed $args Args to pass to function and/or hook
     * @param mixed $additionalInfo Additional informations
     * @param bool $fixOnFail Don't loose arguments data if any hook will fail (return false or null)
     * @return $args Mixed arguments
     */
    
    public function getFeature($featureName, $args='', $additionalInfo=null, $fixOnFail=True)
    {
        $f = preg_replace('/[^\da-zA-Z0-9]/i', '_', $featureName). 'Feature';
        
        if (method_exists($this, $f))
        {
            $args = $this->$f($args, $additionalInfo);
        }
        
        $args = $this -> panthera -> get_filters($featureName, $args, $fixOnFail, $additionalInfo);
        
        return $args;
    }
}
