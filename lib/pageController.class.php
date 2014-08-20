<?php
/**
  * Front controllers related functions
  *
  * @package Panthera\core\system\controllers
  * @author Damian Kęska
  * @license LGPLv3
  */

define('_CONTROLLER_PERMISSION_INLINE_', 2);

/**
 * Abstract interface for front controllers
 *
 * @package Panthera\core\system\controllers
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

    // controller name
    protected $controllerName = null;

    // core classes linked from pantheraCore
    protected $template;
    protected $db;
    protected $user;
    protected $locale;
    protected $session;
    protected $config;

    /**
     * Initialize front controller
     *
     * @return object
     */

    public function __construct ()
    {
        $this -> controllerName = substr(get_called_class(), 0, strpos(get_called_class(), 'Controller'));

        // run pantheraClass constructor to get Panthera Framework object
        parent::__construct();

        // create core classes alias
        $this -> template = &$this -> panthera -> template;
        $this -> db = &$this -> panthera -> db;
        $this -> user = &$this -> panthera -> user;
        $this -> locale = &$this -> panthera -> locale;
        $this -> session = &$this -> panthera -> session;
        $this -> config = &$this -> panthera -> config;

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

        $this -> runModules();
    }

    /**
     * Run modules from modules/frontside/controllername/ directories
     *
     * @return null
     */

    protected function runModules()
    {
        if ($this -> panthera -> cache)
            $cache = $this -> panthera -> cache;
        elseif ($this -> panthera -> varCache)
            $cache = $this -> panthera -> varCache;

        $cacheID = 'front.modules.' .$this->controllerName;
        $files = null;

        $this -> panthera -> logging -> output('Looking for modules in /modules/frontside/' .$this -> controllerName. 'controller/ directory', get_called_class());

        if ($cache and $cache -> exists($cacheID))
        {
            $files = $cache -> get($cacheID);
            $this -> panthera -> logging -> output('Loaded "' .count($files). '" items from cache "' .$cacheID. '"', get_called_class());
        } else {
            $files = array();

            if (is_dir(PANTHERA_DIR. '/modules/frontside/' .$this -> controllerName. 'controller/'))
                $files = array_merge($files, scandir(PANTHERA_DIR. '/lib/modules/frontside/' .$this -> controllerName. 'controller/'));

            if (is_dir(SITE_DIR. '/content/modules/frontside/' .$this -> controllerName. 'controller/'))
                $files = array_merge($files, scandir(SITE_DIR. '/content/modules/frontside/' .$this -> controllerName. 'controller/'));

            if ($cache)
            {
                $cache -> set($cacheID, $files, 86400);
                $this -> panthera -> logging -> output('Saved "' .count($files). '" items to cache "' .$cacheID. '"', get_called_class());
            }
        }

        if ($files)
        {
            foreach ($files as $file)
            {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'php')
                    $this -> panthera -> importModule('frontside/' .$this -> controllerName. 'controller/' .str_replace('.module.php', '', $file));
            }
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
            // don't allow "admin" or "superuser" special permissions to be listed
            if ($permission == 'admin' or $permission == 'superuser')
                return true;

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
            $propsReturn['__methods'][$method->name] = $method->class;

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
            $name. 'AjaxController',
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
     * Check if front controller wasnt included or running directly from PANTHERA_DIR
     * 
     * On success it will construct an object and execute display() on it and return object itself
     * 
     * Example:
     * <code>
     * pageController::runFrontController(__FILE__, 'pa_loginControllerSystem');
     * </code>
     * 
     * @param string $file Input __FILE__
     * @param string $className Controller class name to create instance of
     * @author Damian Kęska
     * @return object|null
     */
    
    public static function runFrontController($file, $className)
    {
        $routingMatches = panthera::getInstance() -> routing -> lastMatched;
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        if ($routingMatches && isset($routingMatches['target']['front']))
            $scriptName = '/' .$routingMatches['target']['front'];
        
        $run = false;
        
        // decide to run the controller or not
        if (is_link($_SERVER['DOCUMENT_ROOT'].$scriptName))
            $run = true;
        else {
            // if cannot detect if it's a symlink or not try with reflection
            $root = $_SERVER['DOCUMENT_ROOT'].str_replace('/route.php', '', $_SERVER['PHP_SELF']);
            $reflection = new ReflectionClass($className);
            
            if (realpath($root.$scriptName) == $reflection -> getFileName())
                $run = true;
        }

        if ($run)
        {
            $object = new $className();
            $object -> display();
            
            return $object;
        }
    }
}

/**
 * Abstract interface for admin front controllers
 *
 * @package Panthera\core\system\controllers
 * @author Damian Kęska
 */

abstract class adminController extends pageController
{
    /**
     * Standard constructor + admin permissions check
     *
     * @author Damian Kęska
     * @return null
     */

    public function __construct()
    {
        $panthera = pantheraCore::getInstance();

        if (!$panthera -> user or !$panthera -> user -> isAdmin())
            pantheraCore::raiseError('forbidden');

        parent::__construct();
    }
}

/**
 * Page controller which includes pantheraFetchDB table integration (add, remove, edit, display data)
 *
 * @package Panthera\core\system\controllers
 * @author Damian Kęska
 */

abstract class dataModelManagementController extends pageController
{
    protected $__dataModelClass = null;
    
    /**
     * New object action form template
     * 
     * @var $__newTemplate
     */
    
    protected $__newTemplate = '';
    
    /**
     * New action form template
     * 
     * @var $__editTemplate
     */
    
    protected $__editTemplate = '';
    
    /**
     * Base template for main view
     * 
     * @var $__baseTemplate
     */
    
    protected $__baseTemplate = '';
    
    /**
     * By default display a list or a item? (list, item)
     * 
     * @var $__defaultDisplay
     */
    
    protected $__defaultDisplay = 'list';
    protected $__listId = 'categoryid';
    protected $__modelIdColumn = 'id';
    
    /**
     * List of specific translation strings like alert box questions
     * This will be passed to template as $lang but array will be converted to translated string
     * 
     * @var $lang
     */
    
    protected $lang = array(
        'deletionConfirm' => array('Are you sure you want to delete this position?', 'admin'),
    );
    
    /**
     * List of fields to display in new/edit template (optional - if not using default template)
     * 
     * Example:
     * <code>
     * $__fields = array(
     *     'title' => array('title' => array('Title', 'domain'), 'type' => 'text'),
     *     'description' => array('title' => 'Description', 'type' => 'wysiwyg'),
     * );
     * </code>
     * 
     * @var $__fields
     */
    
    protected $__fields = array(
        
    );
    
    /**
     * ui.Searchbar object
     * 
     * @var $__searchBar
     */
    
    protected $__searchBar = null;
    protected $__searchBarName = 'uiTop';
    
    /**
     * Columns to search in
     * eg. array('title', 'description') (less = better performance, more = more results and detailed search)
     * 
     * @var $__searchBarQueryColumns
     */
    
    protected $__searchBarQueryColumns = array(
    
    );
    
    /**
     * ui.Pager object
     * 
     * @var $__pager
     */
    
    protected $__pager = null;
    
    /**
     * Remove a object
     *
     * @input int $_POST['objectID'] Object id
     * @author Damian Kęska
     */
    
    public function removeAction()
    {
        $class = $this -> __dataModelClass;
        $object = new $class('id', $_POST['objectID']);
        
        if ($object -> exists())
            $object -> delete();
            
        ajax_exit(array(
            'status' => 'success',
        ));
    }
    
    /**
     * Edit / create new object action
     * Post list of fields eg. object_id, object_title, object_creation etc. with "object_" prefix
     * To insert code and modify this function use hooks (features)
     * Mark save request with $_POST['postData']
     *
     * @input @_POST
     * @author Damian Kęska
     * @return null
     */
    
    public function editAction()
    {
        $class = $this -> __dataModelClass;
        $hookName = str_replace('Ajax', '', get_called_class());
        $hookName = substr($hookName, 0, strpos($hookName, 'Controller'));
        
        if (isset($_GET['objectGroupID']))
            $this -> template -> push('objectGroupID', $_GET['objectGroupID']);
        
        if (isset($_GET['objectID']))
        {
            $object = new $class($this -> __modelIdColumn, $_GET['objectID']);
            
            if ($object -> exists())
            {
                if (isset($_POST['postData']) and $_POST['postData'])
                {
                    $this -> getFeature('datamodel.' .$hookName. '.preedit', $object);
                    
                    if (method_exists($this, 'validateObjectModification'))
                        $this -> validateObjectModification($_POST, $object);
                    
                    // set all available fields
                    foreach ($object -> getData() as $key => $oldValue)
                    {
                        if (isset($_POST['object_' .$key]))
                            $object -> __set($key, $_POST['object_' .$key]);
                    }
                    
                    $this -> getFeature('datamodel.' .$hookName. '.postedit', $object);
                    
                    try {
                        $object -> save();
    
                    } catch (Exception $e) {
                        $this -> getFeature('datamodel.' .$hookName. '.editfailure', $object, $e);
                    
                        ajax_exit(array(
                            'status' => 'failed',
                            'message' => slocalize('Field validation failure, details: %s', 'messages', $e -> getMessage()),
                        ));
                    }
                    
                    ajax_exit(array(
                        'status' => 'success',
                    ));
                }


                // display a edit template form
                $this -> template -> push(array(
                    'action' => 'edit',
                    'fields' => $this -> __fields,
                    'dataObject' => $object,
                ));
                
                $this -> template -> display($this -> __editTemplate);
                pa_exit();
            }

        } else {
            // creating a new object
            if (isset($_POST['postData']) and $_POST['postData'])
            {
                $values = array(

                );
            
                foreach ($_POST as $key => $value)
                {
                    if (strpos($key, 'object_') !== 0)
                        continue;

                    $key = str_replace('object_', '', $key);
                    $values[$key] = $value;
                }
                
                if (method_exists($this, 'validateNewObject'))
                    $this -> validateNewObject($values);
                
                try {
                    $this -> getFeatureRef('datamodel.' .$hookName. '.precreate', $values);
                    $create = $class::create($values);
                    
                } catch (Exception $e) {
                    $this -> getFeatureRef('datamodel.' .$hookName. '.creationfailure', $_POST, $e);
                    
                    ajax_exit(array(
                        'status' => 'failed',
                        'fields' => $this -> __fields,
                        'message' => slocalize('Cannot add new object, field validation failure, details: %s', 'messages', $e -> getMessage()),
                    ));
                }
                
                $this -> getFeature('datamodel.' .$hookName. '.created', $values, $create);
                
                ajax_exit(array(
                    'status' => 'success',
                ));
            }

            // display a new object form
            $this -> template -> push('action', 'edit');
            $this -> template -> display($this -> __newTemplate);
            pa_exit();
        }
    }
    
    /**
     * Display list view
     *
     * @input string $_GET['__filterData'] Column value 
     * @author Damian Kęska
     */
    
    public function __displayList()
    {
        $class = $this -> __dataModelClass;
        $hookName = str_replace('Ajax', '', get_called_class());
        $hookName = substr($hookName, 0, strpos($hookName, 'Controller'));
        
        /**
         * Searchbar and filtering
         */
        
        $filter = new whereClause;
        
        // add searchbar only if selected columns to query via search field
        if ($this -> __searchBarQueryColumns)
        {
            $this -> __searchBar = new uiSearchbar($this -> __searchBarName);
            $this -> __searchBar -> navigate(True);
            
            if ($this -> __searchBarQueryColumns and $this -> __searchBar -> getQuery())
            {
                $filter -> setGroupStatement(2, 'OR');

                foreach ($this -> __searchBarQueryColumns as $column)
                    $filter -> add('OR', $column, 'LIKE', '%' .$this -> __searchBar -> getQuery(). '%', 2);
            }
        }
        
        if (isset($_GET['objectGroupID']) and intval($_GET['objectGroupID']) !== -1)
            $filter -> add('AND', $this -> __listId, '=', $_GET['objectGroupID']);
        
        /**
         * Pagination
         */
         
        $args = $this -> getFeature('datamodel.' .$hookName. '.list.fetchall.args', array($filter, $this -> __pager, false));
        
        if (!is_null($args))
        {
            $tmp = $args[1];
            $args[1] = False;
            $total = call_user_func_array($class. '::fetchAll', $args);
            
            $this -> __pager = new uiPager(get_called_class(), $total, get_called_class(). 'Management', 25);
    
            if ($this -> __pagerTemplatesConfig)
                $this -> __pager -> setLinkTemplatesFromConfig($this -> __pagerTemplatesConfig);
            
            /**
             * Plugins and extensions support
             */
            
            // allow modify filter list by searchbar, next line allows plugins to modify pager
            $this -> getFeatureRef('datamodel.' .$hookName. '.list.filter', $filter);
            $this -> getFeatureRef('datamodel.' .$hookName. '.list.pager', $this -> __pager);
            
            $args[1] = $tmp;
            $this -> getFeatureRef('datamodel.' .$hookName. '.list.fetchargs', $args);
            
            $elements = call_user_func_array($class. '::fetchAll', $args);
            $this -> getFeatureRef('datamodel.' .$hookName. '.list', $elements);
            
            $this -> template -> push(array(
                'fields' => $this -> __fields,
                'foundElements' => $elements,
                'uiPagerName' => get_called_class(),
                'totalElements' => $total,
            ));
        }
    }

    public function __displayItem()
    {
        $class = $this -> __dataModelClass;
        $hookName = str_replace('Ajax', '', get_called_class());
        $hookName = substr($hookName, 0, strpos($hookName, 'Controller'));
        
        $item = new $class($this -> __modelIdColumn, $_GET['objectID']);
        
        $decision = $item -> exists();
        $this -> getFeatureRef('datamodel.' .$hookName. '.item', $decision, $item);
        
        if (!$decision)
            panthera::raiseError('notfound');
        
        $this -> template -> push(array(
            'object' => $item,
        ));
    }
    
    /**
     * Main function
     *
     * @author Damian Kęska
     */
    
    public function display()
    {
        $this -> dispatchAction();
        
        // localization support
        foreach ($this -> lang as &$lang)
            $lang = $this -> panthera -> locale -> localizeFromArray($lang);

        $this -> template -> push(array(
            'lang' => $this -> lang,
        ));
        
        if (isset($_GET['objectGroupID']))
            $this -> template -> push('objectGroupID', $_GET['objectGroupID']);

        if ($this -> __defaultDisplay == 'list')
            $this -> __displayList();
        else
            $this -> __displayItem();

        return $this -> template -> compile($this -> __baseTemplate);     
    }
}
