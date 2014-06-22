<?php
/**
 * Menu editor for Panthera Framework Admin Panel
 *
 * @package Panthera\core\components\simplemenu
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Menu editor for Panthera Framework Admin Panel
 *
 * @package Panthera\core\components\simplemenu
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class menueditAjaxControllerSystem extends pageController
{
    protected $uiTitlebar = array(
        'List of menu categories', 'menuedit',
    );

    protected $permissions = '';
    protected $defaultAction = 'main';

    protected $actionPermissions = array(
        'saveOrder' => array('can_update_menu_{$category}', 'can_update_menus'),
        'saveItem' => array('can_update_menu_{$category}', 'update_menu_item_{$item}', 'can_update_menus'),
        'createItem' => array('can_update_menu_{$category}', 'can_update_menus'),
        'itemRemove' => array('can_update_menu_{$category}', 'update_menu_item_{$item}', 'can_update_menus'),
        'createCategory' => 'can_update_menus',
        'categoryRemove' => array('can_update_menus', 'can_update_menu_{$category}'),
        'getCategory' => array('can_update_menus', 'can_update_menu_{$category}'),
        'getItem' => array('can_update_menu_{$category}', 'update_menu_item_{$item}', 'can_update_menus'),
        'getRoutes' => array('can_update_menus', 'can_update_menu_{$category}', 'update_menu_item_{$item}'),
        'getPreviewRoute' => array('can_update_menus', 'can_update_menu_{$category}', 'update_menu_item_{$item}'),
        'saveCategory' => _CONTROLLER_PERMISSION_INLINE_,
        'main' => _CONTROLLER_PERMISSION_INLINE_,
    );

    protected $requirements = array(
        'arrays',
    );

    /**
     * Main function that should return result
     *
     * @return null
     */

    public function display()
    {
        $this -> pushPermissionVariable('item', $_REQUEST['item_id']);
        $this -> pushPermissionVariable('category', $_REQUEST['category']);

        if (isset($_POST['cat_type']))
            $this -> pushPermissionVariable('category', $_POST['cat_type']);

        $this -> panthera -> locale -> loadDomain('menuedit');
        $this -> panthera -> importModule('simplemenu');
        $this -> dispatchAction();
    }

    /**
     * Save menu order to database
     *
     * @author Damian Kęska
     * @return null
     */

    public function saveOrderAction()
    {
        $order = json_decode($_POST['order']);

        foreach ($order as $orderKey => $id)
        {
            $this -> panthera -> db -> query('UPDATE `{$db_prefix}menus` SET `order`= :orderKey WHERE `id`= :id AND `type` = :category', array(
                'id' => intval($id),
                'orderKey' => intval($orderKey),
                'category' => $_GET['category'],
            ));
        }

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Display a popup with option to add a link
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function quickAddFromPopupAction()
    {
        $link = null;
        
        if (isset($_GET['link']))
        {
            $link = $_GET['link'];
            
            if (substr($link, 0, 5) == 'data:')
                $link = base64_decode(substr($link, strpos($link, 'base64,')+7, strlen($link)));
        }
        
        $title = $_GET['title'];
        
        if (!$this -> panthera -> user -> isAdmin())
            $title = htmlspecialchars($title);
        
        $route['example'] = $route['link'];
        $language = $this -> panthera -> locale -> getFromOverride($_GET['language']);
        $categories = $this -> categoriesSelectBox();
        
        $routeData = null;
        
        if (isset($_GET['routeData']))
            $routeData = unserialize(base64_decode($_GET['routeData']));
        
        $this -> panthera -> template -> push (array(
            'link' => $link,
            'title' => $title,
            'currentLanguage' => $language,
            'categories' => $categories,
            'languages' => $this -> panthera -> locale -> getLocales(),
            'routeData' => $routeData,
        ));

        $this -> panthera -> template -> display('menuedit_quickaddfrompopup.tpl');
        pa_exit();
    }

    /**
     * Save item
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function saveItemAction()
    {
        if (!$_POST['category'] or !$_POST['item_title'])
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please fill all form fields', 'menuedit'),
            ));
        }

        $id = intval($_POST['item_id']);
        $item = new menuItem('id', $id);

        // check if item exists
        if (!$item -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Selected item does not exists', 'menuedit'),
            ));
        }


        if (!$_POST['item_url_id'] or strlen($_POST['item_url_id']) < 3)
            $url_id = seoUrl(strtolower(filterInput($_POST['item_title'], 'quotehtml')));
        else
            $url_id = seoUrl($_POST['item_url_id']);

        // change category if destination category exists
        if ($_POST['category'])
        {
            $category = new menuCategory('type_name', $_POST['category']);

            if ($category -> exists())
            {
                if (!$this->checkPermissions('can_update_menu_' .$category->type_name, TRUE))
                {
                    ajax_exit(array(
                        'status' => 'failed',
                        'message' => slocalize('Cannot save this item to "%s" category, no enough permissions', 'menuedit', $category->title)
                    ));
                }

                $item -> type = $category -> type_name;
            }
        }

        // type: route
        if (intval($_POST['item_type']) == 1)
        {
            if ($this -> panthera -> routing -> exists($_POST['route']))
            {
                $item -> route = $_POST['route'];

                $routeParams = $this -> panthera -> routing -> getParams($_POST['route']);
                $newParams = array();

                foreach ($routeParams as $param)
                {
                    if (isset($_POST['routing_param_' .$param]))
                        $newParams[$param] = $_POST['routing_param_' .$param];
                }

                $item -> routedata = serialize($newParams);
                $item -> routeget = $_POST['routing_get'];
            }
        } else {
            $item -> route = '';
            $item -> routeget = '';
            $item -> routedata = serialize('');
        }

        // set object attributes
        $item -> title = filterInput($_POST['item_title'], 'quotehtml');
        $item -> link = filterInput($_POST['item_link'], 'quotehtml,quotes');
        $item -> url_id = $url_id;
        $item -> tooltip = filterInput($_POST['item_tooltip'], 'quotehtml');
        $item -> icon = filterInput($_POST['item_icon'], 'quotehtml');
        $item -> enabled = (bool)intval($_POST['item_enabled']);

        if (array_key_exists($_POST['item_language'], $this -> panthera -> locale -> getLocales()) or $_POST['item_language'] == 'all')
        {
            $item -> language = $_POST['item_language'];
        }

        $item -> attributes = $_POST['item_attributes'];
        $item -> save();
        simpleMenu::updateItemsCount($_POST['cat_type']);

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Add new item to category
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function createItemAction()
    {
        if (!$_POST['cat_type'] or !$_POST['item_title'])
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please enter a title', 'menuedit')
            ));
        }

        // Check if category exists
        $category = new menuCategory('type_name', $_POST['cat_type']);
        $this -> checkCategoryExists($category);

        // get last item
        $lastItem = simpleMenu::getItems($_POST['cat_type'], 0, 1, 'order', 'desc');

        // if there are any items already stored in database
        if (count($lastItem) > 0)
        {
            $order = intval($lastItem -> order) + 1;
        } else
            $order = 1;



        if (!$_POST['item_url_id'] or strlen($_POST['item_url_id']) < 3)
            $url_id = seoUrl(strtolower(filterInput($_POST['item_title'], 'quotehtml')));
        else
            $url_id = seoUrl($_POST['item_url_id']);
        
        // filter all variables to avoid problems with HTML & JS injection and/or bugs with text inputs
        $title = filterInput($_POST['item_title'], 'quotehtml');
        $attributes = filterInput($_POST['item_attributes'], 'routequotehtml');
        $tooltip = filterInput($_POST['item_tooltip'], 'quotehtml');
        $icon = filterInput($_POST['item_icon'], 'quotehtml');
        $enabled = False;
        
        if (isset($_POST['item_link']))
            $link = filterInput($_POST['item_link'], 'quotehtml,quotes');
        
        $routeName = '';
        $routeParams = '';
        $routeGET = '';

        // route data encoded in base64 (eg. menuedit popup)
        if (isset($_POST['routeEncoded']))
        {
            $route = unserialize(base64_decode($_POST['routeEncoded']));
            
            if (!$route)
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Please select a valid route', 'menuedit'),
                ));
            }
            
            $link = '/';
            $routeName = $route['routeName'];
            
            if (isset($route['params']))
                $routeParams = $route['params'];
            
            if (isset($route['get']))
                $routeGET = $route['get'];
            
            if (isset($route['link']))
                $link = $route['link'];
        }
        
        if (!$link and !$routeName)
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please specify a valid URL address', 'menuedit'),
            ));
        }


        if (!array_key_exists($_POST['item_language'], $this -> panthera -> locale -> getLocales()))
            ajax_exit(array(
                'status' => 'failed',
                'messages' => localize('Invalid language specified', 'menuedit'),
            ));

        $language = $this -> panthera -> locale -> getActive();

        simpleMenu::createItem($_POST['cat_type'], $title, $attributes, $link, $language, $url_id, $order, $icon, $tooltip, $routeName, $routeParams, $routeGET, $enabled);
        simpleMenu::updateItemsCount($_POST['cat_type']);

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Remove item from menu
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function itemRemoveAction()
    {
        $id = intval($_GET['item_id']);
        $item = new menuItem('id', $id);

        if (!$item -> exists())
        {
            ajax_exit(array(
                'status' => 'success',
                'message' => localize('Cannot remove non-existent menu item', 'menuedit'),
            ));
        }

        simpleMenu::removeItem($item -> id);
        simpleMenu::updateItemsCount($item->type);
        unset($item);

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Create a new category
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function createCategoryAction()
    {
        // We cannot create category without title
        if (!$_POST['category_title'])
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please enter a category title', 'menuedit'),
            ));
        }

        // TODO: CATEGORY ALREADY EXISTS!

        $title = filterInput($_POST['category_title'], 'quotehtml');
        $type_name = filterInput($_POST['category_type_name'], 'quotehtml');

        if (!$type_name)
            $type_name = seoUrl($title);

        // filter all variables to avoid problems with HTML & JS injection and/or bugs with text inputs
        $description = filterInput($_POST['category_description'], 'quotehtml');
        $parent = filterInput($_POST['category_parent'], 'quotehtml');

        if ($parent)
        {
            $parentCategory = new menuCategory('type_name', $parent);

            if (!$parentCategory->exists())
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Invalid parent category', 'menuedit'),
                ));
            }

            if (!$this->checkPermissions('can_update_menu_' .$parentCategory->type_name, True))
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => slocalize('No permissions to create a subcategory in "%s"', 'menuedit', $parentCategory->title),
                ));
            }
        }

        // try to create a category
        if (simpleMenu::createCategory($type_name, $title, $description, $parent, 0))
        {
            ajax_exit(array(
                'status' => 'success',
            ));
        }

        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Cannot create new category', 'menuedit'),
        ));
    }

    /**
     * Remove a category
     *
     * @param int $id Optional id (if not given it will take id from $_GET['category_id'])
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function categoryRemoveAction($id=null)
    {
        $category = new menuCategory('type_name', $_GET['category']);
        $this -> checkCategoryExists($category);

        simpleMenu::removeCategory($category -> id);

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Generate a select box for categories
     *
     * @author Damian Kęska
     * @return array
     */

    protected function categoriesSelectBox($array=null, $depth=0, $result, $lastID=null)
    {
        if (!$array)
        {
            $depth = 0;
            $first = True;
            $array = menuCategory::fetchTree();
        }

        foreach ($array as $key => $value)
        {
            $str = '';

            if (isset($first))
                $depth = 0;

            if ($lastID)
            {
                if ($lastID -> type_name != $value['item'] -> parent)
                    $depth = 0;
            }

            $depth++;

            if ($depth > 1)
                $str = str_repeat('--', $depth). ' ';

            if ($this->checkPermissions('can_update_menu_' .$value['item']->type_name, TRUE))
                $result[$value['item']->type_name] = $str.$value['item'] -> title;

            if ($value['subcategories'])
                $result = $this -> categoriesSelectBox($value['subcategories'], $depth, $result, $value['item']);

            $lastID = $value['item'];
        }

        return $result;
    }

    /**
     * Get list of routes
     *
     * @author Damian Kęska
     * @return null
     */

    public function getRoutesAction()
    {
        $routes = $this -> panthera -> routing -> getRoutes();
        $params = array();

        if (isset($_GET['route']))
        {
            if ($_GET['route'])
            {
                try {
                    $params = $this -> panthera -> routing -> getParams($_GET['route']);
                } catch (Exception $e) {

                    ajax_exit(array(
                        'status' => 'failed',
                        'message' => localize('Invalid route name', 'menuedit'),
                        'details' => $e->getMessage(),
                    ));
                }
            }
        }

        foreach ($routes as $key => &$value)
            $value = null;

        ajax_exit(array(
            'status' => 'success',
            'routes' => $routes,
            'params' => $params,
        ));
    }

    /**
     * Calculate preview route
     *
     * @author Damian Kęska
     * @return null
     */

    public function getPreviewRouteAction()
    {
        try {
            $result = $this -> panthera -> routing -> generate($_POST['route'], @json_decode($_POST['params'], TRUE), $_POST['getparams']);

        } catch (Exception $e) {

            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid route name', 'menuedit'),
                'details' => $e->getMessage(),
            ));
        }

        ajax_exit(array(
            'status' => 'success',
            'url' => $result,
        ));
    }

    /**
     * Menu item page
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function getItemAction()
    {
        $item = new menuItem('id', intval($_GET['item_id']));

        if (!$item -> exists())
        {
            // display no access page
            $this -> checkPermissions(false);
        }

        $category = new menuCategory('type_name', $item -> type);
        $routeData = unserialize($item->routedata);

        $this -> panthera -> template -> push (array(
            'item_title' => $item -> title,
            'item_url_id' => $item -> url_id,
            'item_link' => $item -> link,
            'item_tooltip' => $item -> tooltip,
            'item_icon' => $item -> icon,
            'item_attributes' => $item -> attributes,
            'item_id' => $item -> id,
            'item_language' => $item -> language,
            'cat_type' => $item -> type,
            'item_category' => $item -> type,
            'item_category_name' => $category->title,
            'categories' => $this -> categoriesSelectBox(),
            'category' => $item -> type,
            'routes' => $this -> panthera -> routing -> getRoutes(),
            'route' => $item -> route,
            'routing_get' => $item -> routeget,
            'routedata' => $routeData,
            'linkPreview' => '',
            'enabled' => (bool)$item -> enabled,
        ));

        if ($item -> route and is_array($routeData))
        {
            try {
                $this -> panthera -> template -> push('linkPreview', $this -> panthera -> routing -> generate($item->route, $routeData, $item->routeget));
            } catch (Exception $e) { }
        }

        $locales = array();

        foreach ($this -> panthera -> locale -> getLocales() as $key => $value)
        {
            // skip hidden locales
            if ($value == False)
                continue;

            $active = False;

            // if this is a current set locale
            if ($item -> language == $key)
                $active = True;

            $locales[$key] = $active;
        }

        $this -> panthera -> template -> push('item_language', $locales);
        $this -> panthera -> template -> push('action', 'item');

        $titlebar = new uiTitlebar(localize('Editing item', 'menuedit'). " - ". $item -> title);
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png', 'left');

        $this -> panthera -> template -> display('menuedit_item.tpl');
        pa_exit();
    }

    /**
     * Show category items
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     */

    public function getCategoryAction()
    {
        $category = new menuCategory('type_name', $_GET['category']);
        $this -> checkCategoryExists($category);

        $items = simpleMenu::getItems($_GET['category']);
        $array = array();

        foreach ($items as $key => $value)
        {
            $tmp = $value;
            $tmp['link_original'] = $value['link'];
            $tmp['link'] = pantheraUrl($value['link'], false, 'frontend');

            if ($tmp['route'])
            {
                $tmp['link_original'] = $tmp['link'] = $this -> panthera -> routing -> generate($tmp['route'], unserialize($tmp['routedata']), $tmp['routeget']);
            }

            $array[$key] = $tmp;
        }

        $locales = array();

        foreach ($this -> panthera -> locale -> getLocales() as $key => $value)
        {
            // skip hidden locales
            if ($value == False)
                continue;

            $active = False;

            // if this is a current set locale
            if ($item -> language == $key)
                $active = True;

            $locales[$key] = $active;
        }

        $this -> panthera -> template -> push(array(
            'item_language' => $locales,
            'cat_type' => $_GET['category'],
            'menus' => $array,
            'category' => $_GET['category'],
            'newItemButton' => $this -> checkPermissions('can_update_menu_' .$category->cat_name, TRUE),
            'editCategoryButton' => $this -> checkPermissions('can_update_menu_' .$category->cat_name, TRUE),
            'action' => 'edit',
            'object' => $category,
            'categoriesSelectBox' => $this -> categoriesSelectBox(),
            'ref' => filterInput($_GET['ref'], 'quotehtml'),
        ));

        // update elements count if there is any mismatch
        if (count($array) != intval($category->elements))
        {
            $category -> elements = count($array);
            $category -> save();
        }

        $titlebar = new uiTitlebar(localize('Edit menu', 'menuedit')." (".localize('To change sequence of items in the category, you can drag & drop them', 'menuedit').")");
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png', 'left');

        if ($_GET['subaction'] == 'edit')
            $this -> panthera -> template -> display('menuedit_edit_category.tpl');
        else
            $this -> panthera -> template -> display('menuedit_category.tpl');

        pa_exit();
    }

    /**
     * This method simply checks if category exists, if not its executing ajax_exit
     *
     * @param menuCategory $category Category object
     * @return bool
     */

    protected function checkCategoryExists($category)
    {
        if (!$category->exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Category not found', 'menuedit'),
            ));
        }

        return TRUE;
    }

    public function saveCategoryAction()
    {
        $category = new menuCategory('id', $_POST['category_id']);
        $this -> checkCategoryExists($category);

        $_POST['category_type_name'] = seoUrl($_POST['category_type_name']);

        // check required permissions: 1. Can edit current category 2. Can edit destination, 3. Can edit all categories
        $permissions = array(
            'can_update_menu_' .$category->type_name,
            'can_update_menus',
        );

        if ($_POST['category_type_name'] != $category->type_name)
        {
            $permissions[] = 'can_update_menu_' .$_POST['category_type_name'];

            $destination = new menuCategory('type_name', $_POST['category_type_name']);

            // check if destination type_name already exists
            if ($destination -> exists())
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => slocalize('Category with ID "%s" already exists and its named "%s"', 'menuedit', $_POST['category_type_name'], $destination->title),
                ));
            }
        }

        // changing parent category: 1. Check if exists, 2. Check if we have permissions
        if ($_POST['category_parent'] != $category -> parent and $_POST['category_parent'])
        {
            $parent = new menuCategory('type_name', $_POST['category_parent']);

            if (!$parent -> exists())
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Invalid parent category', 'menuedit'),
                ));
            }

            $permissions[] = 'can_update_menu_' .$parent->type_name;
        }

        $this->checkPermissions($permissions);

        if ($_POST['category_type_name'])
            $category -> type_name = $_POST['category_type_name'];

        if ($_POST['category_title'])
            $category -> title = filterInput($_POST['category_title'], 'quotehtml');

        if ($_POST['category_parent'] != $category -> parent)
            $category -> parent = $_POST['category_parent'];

        $category -> description = filterInput($_POST['description'], 'quotehtml');
        $category -> save();

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Main action
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function mainAction()
    {
        $categories = menuCategory::fetchTree();

        // show only categories user have access to
        arrays::arrayWalkRecursive($categories, function ($key, &$value, $depth, $this) {
            if (is_object($value) and !$this->checkPermissions('can_update_menu_' .$value->type_name, TRUE))
                $value = null;
        });

        $this -> panthera -> template -> push(array(
            'menu_categories' => $categories,
            'newCategoryButton' => $this -> checkPermissions('can_update_menus', TRUE),
            'categoriesSelectBox' => $this -> categoriesSelectBox(),
        ));

        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png', 'left');
        $this -> panthera -> template -> display('menuedit.tpl');
        pa_exit();
    }
}