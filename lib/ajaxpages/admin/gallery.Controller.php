<?php
/**
 * Gallery management
 * Images and albums management via admin panel
 *
 * @package Panthera\core\components\gallery
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Gallery management
 * Images and albums management via admin panel
 *
 * @package Panthera\core\components\gallery
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class galleryAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.datasheet',
        'admin/ui.pager',
    );

    protected $uiTitlebar = array();

    protected $userPermissions = array();

    protected $actionPermissions = array(
        'addCategory' => array('can_manage_galleries'),
        'addItem' => array('can_manage_galleries', 'can_manage_gallery_{$category}'),
        'addUploads' => array('can_manage_galleries', 'can_manage_gallery_{$category}'),
        'createCategory' => array('can_manage_galleries'),
     // 'deleteCategory' => array('can_manage_galleries', 'can_manage_gallery_{$category}'),
        'displayCategory' => array('can_manage_galleries', 'can_read_all_galleries', 'can_manage_gallery_{$category}'),
        'editCategory' => array('can_manage_galleries', 'can_manage_gallery_{$category}'),
        'saveCategoryDetails' => array('can_manage_galleries', 'can_manage_gallery_{$category}'),
        'setCategoryThumb' => array('can_manage_galleries', 'can_manage_gallery_{$category}')
    );



    /**
     * Save category details to database
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function saveCategoryDetailsAction()
    {
        // get gallery by given id
        $gallery = new galleryCategory('id', intval($_GET['categoryid']));

        // be sure that given gallery exists
        if (!$gallery -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('There is no such category', 'gallery')));

        // if gallery's language is different to $_POST['language'], should copy category to given language
        if ($gallery->language != $_POST['language'])
        {
            $statement = new whereClause();
            $statement -> add('', 'unique', '=', $gallery->unique);
            $statement -> add('AND', 'language', '=', $_POST['language']);

            $checkCategory = new galleryCategory($statement, null);

            if ($checkCategory -> exists())
                ajax_exit(array('status' => 'failed', 'message' => localize('Category in this language already exists!', 'gallery')));

            // TODO: saveCategoryDetailsAction, create/copy category to other language
        }


        // language
        if ($this -> panthera -> locale -> exists($_POST['language']))
            $gallery -> language = $_POST['language'];

        // title
        if (strlen($_POST['title']) > 0)
            $gallery -> title = $_POST['title'];

        // visibility
        if ($_POST['visibility'] == True)
            $gallery -> visibility = True;
        else
            $gallery -> visibility = False;

        // all_langs parameter - one category, all languages
        if (isset($_POST['all_langs']))
            $gallery -> meta('unique') -> set('all_langs', $gallery->id);
        else
            $gallery -> meta('unique') -> set('all_langs', False);

        // hook - plugins are able to change gallery variables
        $gallery = $this -> panthera -> get_filters('admin.gallery.save', $gallery, true);

        // save changes
        $gallery -> meta('unique') -> save();
        $gallery -> save();

        ajax_exit(array('status' => 'success', 'language' => $gallery->language, 'unique' => $gallery->unique));
    }



    /**
     * Delete category from database
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function deleteCategoryAction()
    {
        // recognize type of given variable
        if (strpos($_GET['categoryid'], '.'))
            $id = explode(".", $_GET['categoryid']);
        else
            $id = intval($_GET['categoryid']);

        // if got more than one id, call special function
        if (is_array($id))
            $this -> deleteCategories($id);

        // check user permission
        $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$id));

        // delete category
        if (gallery::removeCategory($id))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'messages')));
    }



    /**
     * Delete categories from database (for more than one)
     *
     * @param $ids array, contains ids of categories to delete
     * @author Mateusz Warzyński
     * @return null
     */

    protected function deleteCategories($ids)
    {
        // check general user permissions
        if (!$this -> userPermissions['manageAll'])
        {
            foreach ($ids as $id) {
                // check for special permissions
                if (!$this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$id), True))
                    ajax_exit(array('status' => 'failed', 'message' => localize("You haven't enough permission to delete choosen categories", 'gallery')));
            }
        }

        // set control variable
        $notRemoved = 0;

        // delete categories (and items which belong to categories)
        foreach ($id as $i) {
            // execute action, check if result is correct
            if (!gallery::removeCategory($i))
                $notRemoved++;
        }

        // if something went wrong, then display warning and return number of unsuccessfull actions
        if ($notRemoved)
            ajax_exit(array('status' => 'failed', 'message' => localize("Some categories haven't been deleted!", 'gallery'), 'amount' => $notRemoved));

        ajax_exit(array('status' => 'success'));
    }



    /**
     * Add uploaded files to gallery (drag&drop)
     *
     * @input json {1, 2, 3, 4, 10, 50, 60} - upload id's
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function addUploadsAction()
    {
        // get ids (of files) from decoded json array
        $files = json_decode($_POST['ids']);

        // set control variable
        $notAdded = 0;

        foreach ($files as $id)
        {
            // get uploaded file
            $file = new uploadedFile('id', $id);

            if ($file -> exists())
            {
                // check file's mime type, only images will be added
                if (filesystem::fileTypeByMime($file->mime) != 'image') {
                    $notAdded++;
                    continue;
                }

                galleryItem::createGalleryItem(basename($file -> location),
                    $file -> description,
                    pantheraUrl($file -> getLink(), True),
                    intval($_GET['categoryid']),
                    True,
                    $file,
                    $this -> panthera -> user -> id,
                    $this -> panthera -> user -> login
                );
            }
        }

        // if something went wrong, return amount of not added files
        if ($notAdded > 0)
            ajax_exit(array('status' => 'failed', 'count' => $notAdded)); // TODO: addUploadsAction, change message in template (now notAdded amount is returned)

        ajax_exit(array('status' => 'success'));
    }



    /**
     * Toggle visibility of category
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function toggleGalleryVisibilityAction()
    {
        // recognize type of given value
        if (strpos($_GET['categoryid'], '.'))
            $id = explode(".", $_GET['categoryid']);
        else
            $id = intval($_GET['categoryid']);

        if (is_array($id))
            $this -> toggleGalleriesVisibility($id);


        // check permissions
        $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$id));

        // get item by id
        $item = new galleryCategory('id', $id);

        // check if item exists
        if (!$item -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Category does not exist!')));

        // toggle visibility and save
        $item -> visibility = !(bool)$item->visibility;
        $item -> save();

        ajax_exit(array('status' => 'success', 'visible' => $item -> visibility));
    }

    /**
     * Toggle visibility of categories (for more than one)
     *
     * @author Mateusz Warzyński
     * @return null
     */

    protected function toggleGalleriesVisibility($ids)
    {
        // check general permission
        if (!$this -> userPermissions['manageAll'])
        {
            foreach ($ids as $i) {
                // check special permissions
                if (!$this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$i), True))
                    ajax_exit(array('status' => 'failed', 'message' => localize(), 'id' => $i));
            }
        }

        /* This function was created to set visibility for categories of the same unique key */

        // get represent category object
        $item = new galleryCategory('id', $id[0]);

        // in case if first id is invalid
        if (!$item -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Error occured!')));

        // get main value of visibility
        $visibility = !(bool)$item->visibility;

        // change visibility of represent category and save
        $item -> visibility = $visibility;
        $item -> save();

        // prevent doing the same action in loop
        unset($id[0]);

        // set the same visibility for all categories
        foreach ($id as $i) {
            // get category
            $item = new galleryCategory('id', $i);

            // check if item exists, change visibility and save
            if ($item -> exists()) {
                $item -> visibility = $visibility;
                $item -> save();
            }
            // clean variable before next action of deleting
            unset($item);
        }

        ajax_exit(array('status' => 'success', 'visible' => $visibility));
    }



    /**
     * Create gallery
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function createCategoryAction()
    {
        // check general permission
        $this -> checkPermissions('can_manage_galleries');

        // check if all fields are filled
        if (!$_POST['name'] or !isset($_POST['visibility']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Please fill all form fields', 'gallery')));


        if (gallery::createCategory(htmlspecialchars($_POST['name']), $this->panthera->user->login, $this->panthera->user->id, $_POST['language'], intval($_POST['visibility']), $this->panthera->user->full_name))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'error' => localize('Unknown error', 'gallery')));
    }



    /**
     * Action executed if switch gallery wants to display category which does not exist
     *
     * @param $unique string, old category unique
     * @param $language string, new category will be assigned to this language
     * @author Mateusz Warzyński
     * @return null
     */

    protected function switchCategory($unique, $language)
    {
        if (!$this -> userPermissions['manageAll'])
            return False;

        $category = new galleryCategory('unique', $unique);

        if (!$category->exists())
            return False;

        // create a category in a new language
        gallery::createCategory($category->title, $this->panthera->user->login, $this->panthera->user->id, $language, 0, $this->panthera->user->full_name, $category->unique);

        $statement = new whereClause();
        $statement -> add('', 'unique', '=', $unique);
        $statement -> add('AND', 'language', '=', $language);

        $newCategory = new galleryCategory($statement, null);
        $newCategory -> thumb_url = $category -> thumb_url;
        $newCategory -> thumb_id = $category -> thumb_id;

        // copy items to created gallery (from existing one)
        galleryItem::copyGalleryItems($newCategory->id, $category->id);

        // get back new category object
        return $newCategory;
    }



    /**
     * Display images of given category
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function displayCategoryAction()
    {
        // implement searchbar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?display=gallery&cat=admin&action=displayCategory&unique='.$_GET['unique'].'&language='.$_GET['language']);
        $sBar -> navigate(True);

        // add options for order (specify column to search)
        $sBar -> addSetting('order', localize('Order by', 'search'), 'select', array(
                'id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
                'title' => array('title' => localize('Title', 'gallery'), 'selected' => ($_GET['order'] == 'title')),
                'description' => array('title' => localize('Description', 'gallery'), 'selected' => ($_GET['order'] == 'description'))
            ));

        // add options for direction of results (ASC, DESC)
        $sBar -> addSetting('direction', localize('Direction', 'search'), 'select', array(
                'ASC' => array('title' => localize('Ascending', 'search'), 'selected' => ($_GET['direction'] == 'ASC')),
                'DESC' => array('title' => localize('Descending', 'search'), 'selected' => ($_GET['direction'] == 'DESC'))
            ));

        // build a query using `unique` and `language` columns
        $statement = new whereClause();
        $statement -> add('', 'unique', '=', $_GET['unique']);
        $statement -> add('AND', 'language', '=', $_GET['language']);

        // create category object
        $category = new galleryCategory($statement, null);

        // add option to enable setting special permissions
        $sBar -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), '#', '?display=acl&cat=admin&popup=true&name=can_manage_gallery_'.$category->id, localize( 'Manage permissions' ) );

        // check all_lang parameter
        if (intval($category -> meta('unique') -> get('all_langs')) > 0) {
            // check if $category is marked
            if (intval($category -> meta('unique') -> get('all_langs')) != intval($category -> id))
            {
                // load other category which is marked as for all languages
                $ctg = new galleryCategory('id', $category -> meta('unique') -> get('all_langs'));

                // replace category by marked one
                if ($ctg -> exists())
                    $category = $ctg;
             }
        }

        // switch category by language
        if (!$category -> exists())
            $category = $this->switchCategory($_GET['unique'], $_GET['language']);

        if ($category === False)
            pa_exit();

        // search variables - define available (and default) options
        $order = 'id'; $orderColumns = array('id', 'title', 'description');
        $direction = 'DESC';

        // order by
        if (in_array($_GET['order'], $orderColumns))
            $order = $_GET['order'];

        // direction
        if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
            $direction = $_GET['direction'];

        // build statement to get images
        $w = new whereClause();
        $w -> add( 'AND', 'gallery_id', '=', $category -> id);

        // check if user used searchbar
        if ($_GET['query'])
        {
            $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase

            if ($order != 'id')
                $w -> add( 'AND', $order, 'LIKE', '%' .$_GET['query']. '%');
            else
                $w -> add( 'AND', $order, '=', $_GET['query']);
        }

        // if does not exist in cache...
        if (!isset($totalItems))
            $totalItems = galleryItem::getGalleryItems($w, False, False, $order); // ...add it!

        // get page
        $page = $_GET['page'];

        // pager implementation
        $uiPager = new uiPager('adminGalleryItems', $totalItems, 'adminGalleryItems', 16);
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\');');

        // get limites
        $limit = $uiPager -> getPageLimit();

        // get images using earlier built statement and pager options
        $items = galleryItem::getGalleryItems($w, $limit[1], $limit[0], $order, $direction);

        // send information about category to template
        $this -> panthera -> template -> push('category_title', $category -> title);
        $this -> panthera -> template -> push('category_id', $category -> id);
        $this -> panthera -> template -> push('item_list', $items);
        $this -> panthera -> template -> push('category_language', $category -> language);
        $this -> panthera -> template -> push('unique', $_GET['unique']);
        $this -> panthera -> template -> push('languages', $this -> panthera -> locale -> getLocales());
        $this -> panthera -> template -> push('galleryObject', $category);
        $this -> panthera -> template -> push('page', $page);

        // if all_langs is enable, send it!
        if (intval($category -> meta('unique') -> get('all_langs')) > 0)
            $this -> panthera -> template -> push('all_langs', True);

        // get custom styles for gallery in both languages and for gallery in single language
        $header = $category -> meta('unique') -> get('site_header');

        if ($category -> meta('id') -> get('site_header') != null)
            $header = array_merge($header, $category->meta('unique')->get('site_header'));

        // add custom styles and scripts
        if (count($header) > 0)
        {
            if (count($header['scripts']) > 0)
            {
                foreach ($header['scripts'] as $key => $value)
                    $this -> panthera -> template -> addScript($value);
            }

            if (count($header['styles']) > 0)
            {
                foreach ($header['styles'] as $key => $value)
                    $this -> panthera -> template -> addStyle($value);
            }
        }

        if ($category -> visibility)
            $visibility = localize("visible", 'gallery');
        else
            $visibility = localize("invisible", 'gallery');

        // set titlebar
        $titlebar = new uiTitlebar($category->title . " (".$category->language.", ".$visibility.")");

        $this -> panthera -> template -> push('category_visibility', $category -> visibility);
        $this -> panthera -> template -> push('current_lang', $category -> language);

        $this -> panthera -> template -> display('gallery_displaycategory.tpl');
        pa_exit();
    }




    /**
     * Creating new category
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     */

    public function addCategoryAction()
    {
        if ($_GET['new_title'])
        {
            gallery::createCategory($_GET['filter'].$_GET['new_title'], $this->panthera->user->login, $this->panthera->user->id, $this->panthera->user->language, intval($_GET['visibility']), $this->pantherauser->full_name, md5(rand(999, 9999)));
            ajax_exit(array('status' => 'success'));
        } else {
            ajax_exit(array('status' => 'failed', 'error' => localize('Title cannot be empty', 'gallery')));
        }
    }

    /**
     * Setting gallery thumbnail from gallery image
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     */

    public function setCategoryThumbAction()
    {
        $ctgid = intval($_GET['categoryid']);

        $item = new galleryItem('id', intval($_GET['itemid']));
        $category = new galleryCategory('id', $ctgid);

        // if item and gallery exist set default thumbnail
        if ($item->exists() and $category->exists()) {
             $category -> thumb_id = $item -> id;
             $category -> thumb_url = $item -> link;
             $category -> save();
             ajax_exit(array('status' => 'success'));
        } else {
             ajax_exit(array('status' => 'failed', 'error' => localize('Error with changing gallery thumbnail!', 'gallery')));
        }
    }

    /**
     * Edit category, save variables to database
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     */

    public function editCategoryAction()
    {
        $id = intval($_GET['categoryid']);

        // get category as object
        $item = new galleryCategory('id', $id);

        if (!$item -> exists()) {
            ajax_exit(array('status' => 'failed', 'error' => localize('Category does not exists')));
            pa_exit();
        }

        // create response as array, easy way to return what have been changed
        $response = array('status' => 'success');

        // set title
        if (strlen($_GET['new_title']) > 3) {
            $item -> title = filterInput($_GET['new_title'], 'quotehtml');
            $response['title'] = filterInput($_GET['new_title'], 'quotehtml');
        } else {
            ajax_exit(array(
               'status' => 'failed',
               'error' => localize("Title is too short or empty", 'gallery')
            ));
        }

        // set visibility
        if (isset($_GET['visibility'])) {

            if ($_GET['visibility'] == 'show') {
                $item -> visibility = True;
                $response['visibility'] = 'show';
            } else {
                $item -> visibility = False;
                $response['visibility'] = 'hide';
            }

        }

        // save changes
        $item -> save();

        // send response to template
        ajax_exit($response);

        pa_exit();
    }

    /**
     * Edit item form
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public function editItemFormAction()
    {
        if ($_GET['subaction'] == 'editItem')
        {
            $item = new galleryItem('id', intval($_GET['itemid']));
            $_POST['upload_id'] = intval($_POST['upload_id']);

            // check permissions
            if (!$this -> userPermissions['manageAll'])
                $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gimage_'.$id, 'can_manage_gallery_'.$item->getGalleryID()));

            // check if image exists
            if (!$item -> exists())
                ajax_exit(array('status' => 'failed', 'error' => localize('Error with changing item!')));

            // got uploaded file
            $file = new uploadedFile('id', $_POST['upload_id']);

            // check if got file exists
            if (!$file -> exists())
                ajax_exit(array('status' => 'failed', 'message' => localize('Selected file doesnt exists in upload list', 'gallery')));

            // set item visibility
            if ($_POST['visibility'] == '1')
                $item -> visibility = 0;
            else
                $item -> visibility = 1;

            $item -> title = filterInput($_POST['title'], 'quotehtml');
            $item -> description = filterInput($_POST['description'], 'quotehtml');
            $item -> link = pantheraUrl($file -> getLink());
            $item -> thumbnail = $file -> getThumbnail($this->panthera->config->getKey('gallery_thumbs_width', 200, 'int'), True);
            $item -> upload_id = $_POST['upload_id'];

            $category = new galleryCategory('id', $_POST['gallery_id']);

            if ($category -> exists())
                $item -> gallery_id = $_POST['gallery_id'];

            $item -> save();

            ajax_exit(array('status' => 'success', 'unique' => $item -> unique));
        }


        // get gallery image
        $item = new galleryItem('id', intval($_GET['itemid']));

        // check if image exists
        if (!$item -> exists())
            pa_exit();

        // check permissions
        if (!$this -> userPermissions['manageAll'])
            $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gimage_'.$id, 'can_manage_gallery_'.$item->getGalleryID()));

        // send information about image to template
        $this -> panthera -> template -> push('id', $item -> id);
        $this -> panthera -> template -> push('title', $item -> title);
        $this -> panthera -> template -> push('description', $item -> description);
        $this -> panthera -> template -> push('link', pantheraUrl($item -> link));
        $this -> panthera -> template -> push('thumbnail', pantheraUrl($item -> thumbnail));
        $this -> panthera -> template -> push('gallery_id', $item -> gallery_id);
        $this -> panthera -> template -> push('visibility', $item -> visibility);
        $this -> panthera -> template -> push('upload_id', $item -> upload_id);
        $this -> panthera -> template -> push('page', $_GET['page']);

        // get gallery categories list and send it to template
        $c = gallery::fetch('');
        $this -> panthera -> template -> push('category_list', $c);

        // get information about gallery category to whose image is assigned
        $category = new galleryCategory('id', $item -> gallery_id);
        $this -> panthera -> template -> push('unique', $category -> unique);
        $this -> panthera -> template -> push('language_item', $category -> language);

        // set titlebar
        $titlebar = new uiTitlebar(localize('Editing gallery image', 'gallery'));

        // compile html code!
        $this -> panthera -> template -> display('gallery_edititem.tpl');
        pa_exit();
    }

    /**
     * Adding item form
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return string
     */

    public function addItemAction()
    {
        if ($_GET['subaction'] == 'add')
        {
            if (strlen($_POST['title']) > 0 and strlen($_POST['categoryid']) > 0 and strlen($_POST['upload_id']) > 0)
            {
                // validate input
                $_POST['title'] = filterInput($_POST['title'], 'quotehtml');
                $_POST['description'] = filterInput($_POST['description'], 'quotehtml');
                $uploadID = intval($_POST['upload_id']);
                $visibility = intval((bool)intval($_POST['visibility']));
                $galleryID = intval($_POST['categoryid']);

                // validate category
                $category = new galleryCategory('id', $galleryID);

                //  check if gallery exists
                if (!$category->exists())
                    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find destination category you want to save image to', 'gallery')));

                // check if uploaded file exists
                $file = new uploadedFile('id', $uploadID);

                // check if uploaded file exists
                if (!$file -> exists())
                    ajax_exit(array('status' => 'failed', 'message' => localize('Selected file does not exists in list of uploaded files', 'gallery')));

                $link = pantheraUrl($file->getLink(), True);

                if (galleryItem::createGalleryItem($_POST['title'], $_POST['description'], $link, $galleryID, $visibility, $file, $this->panthera->user->id, $this->panthera->user->login))
                    ajax_exit(array('status' => 'success', 'ctgid' => $galleryID));
                else
                    ajax_exit(array('status' => 'failed', 'message' => localize('Database error, please refresh this page and try again', 'messages')));

            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Please fill all form inputs', 'gallery')));
            }

            pa_exit();
        }

        $id = intval($_GET['categoryid']);

        // get list of available gallery categories
        $c = gallery::fetch('');

        // get information about current gallery category
        $category = new galleryCategory('id', $id);

        if (!$category -> exists())
            pa_exit();

        $this -> panthera -> template -> push('category_list', $c);
        $this -> panthera -> template -> push('category_id', $_GET['categoryid']);
        $this -> panthera -> template -> push('gallery_name', $category->title);
        $this -> panthera -> template -> push('unique', $category->unique);
        $this -> panthera -> template -> push('language_item', $category->language);

        // set titlebar
        $titlebar = new uiTitlebar(localize('Adding gallery image', 'gallery'));

        // display page
        $this -> panthera -> template -> display('gallery_additem.tpl');
        pa_exit();
    }




    /**
     * Delete an image from gallery
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function deleteItemAction()
    {
        // get id from $_GET
        $id = intval($_GET['itemid']);

        // create image object
        $item = new galleryItem('id', $id);

        // check if item exists
        if (!$item -> exists())
            ajax_exit(array('status' => 'failed', 'error' => localize('Item does not exist!', 'gallery')));

        // manage all galleries and images, manage selected gallery, manage selected image
        $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$item->getGalleryID(), 'can_manage_gimage_'.$id));

        // delete image
        if (gallery::removeImage($id))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'error' => localize('Database error, please refresh the page and try again', 'messages')));
    }



    /**
     * Remove images from category (for more than one image)
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function deleteItemsAction()
    {
        if (!isset($_GET['itemid']))
            pa_exit();

        // create an array from given string
        $ids = explode(",", $_GET['itemid']);

        // define control variable
        $notDeleted = 0;

        foreach ($ids as $id)
        {
            // get image by id
            $item = new galleryItem('id', $id);

            // display that there is no access when there is no such item
            if (!$item -> exists())
                continue;

            // manage all galleries and images, manage selected gallery, manage selected image
            if (!$this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$item->getGalleryID(), 'can_manage_gimage_'.$id), True)) {
                $notDeleted++;
                continue;
            }

            if (!gallery::removeImage($id))
                $notDeleted++;
        }

        // return a number of not deleted images
        if ($notDeleted)
            ajax_exit(array('status' => 'failed', 'error' => localize("Some images have not been deleted!", 'gallery'), 'number' => $notDeleted));
        else
            ajax_exit(array('status' => 'success'));
    }



    /**
     * Toggle image visibility
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function toggleItemVisibilityAction()
    {
        if (!isset($_GET['itemid']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Please, specify the category.', 'gallery')));

        // get image by id
        $item = new galleryItem('id', intval($_GET['itemid']));

        // check if image exists
        if (!$item -> exists())
            ajax_exit(array('status' => 'failed', 'error' => localize('Item does not exists')));

        // check general permissions
        if (!$this -> userPermissions['manageAll'])
            // check special permissions
            $this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$item->getGalleryID(), 'can_manage_gimage_'.$id));

        // toggle image visibility and save
        $item -> visibility = !(bool)$item -> visibility;
        $item -> save();

        ajax_exit(array('status' => 'success', 'visible' => $item -> visibility));
    }


    /**
     * Toggle images visibility (for more than one)
     *
     * @author Mateusz Warzyński
     * @return null
     */

    protected function toggleItemsVisibilityAction()
    {
        // create an array
        $ids = explode(",", $_GET['itemid']);

        // define control variable
        $notToggled = 0;

        foreach ($ids as $id)
        {
            $item = new galleryItem('id', $id);

            // check existance
            if (!$item -> exists()) {
                $notToggled++;
                continue;
            }

            // check permissions
            if (!$this -> checkPermissions(array('can_manage_galleries', 'can_manage_gallery_'.$item->getGalleryID(), 'can_manage_gimage_'.$id))) {
                $notToggled++;
                continue;
            }

            // change visibility and save
            $item -> visibility = !(bool)$item->visibility;
            $item -> save();
        }

        // just to check whether everything is correct
        if ($notToggled)
            ajax_exit(array('status' => 'failed', 'error' => localize('Some images have not been toggled.'), 'number' => $notToggled));
        else
            ajax_exit(array('status' => 'success'));
    }



    /**
     * Displays results
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        // get user permissions
        $this -> userPermissions = array(
            "manageAll" => getUserRightAttribute($this -> panthera -> user, 'can_manage_galleries')
        );

        /* Prepare for checking permissions for action functions manually
        $this -> pushPermissionVariable('item', $_REQUEST['item_id']);
        $this -> pushPermissionVariable('category', $_REQUEST['category']);
        */

        // load language domain
        $this -> panthera -> locale -> loadDomain('gallery');

        // check if action is given
        $this -> dispatchAction();

        // here we will store query and other filter params (eg. language)
        $filter = array();

        // implement searchbar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?' .getQueryString('GET', '', array('_', 'page', 'query')));
        $sBar -> navigate(True);
        $sBar -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), '#', '?display=acl&cat=admin&popup=true&name=can_manage_galleries,can_read_own_galleries,can_read_all_galleries', localize('Manage permissions'));

        // get available languages in panthera
        $languages = $this -> panthera -> locale -> getLocales();

        // if user changed language, check if it's supported
        if ($_GET['language'])
        {
            // check if given language exists
            if ($languages[$_GET['language']]) {
                $this -> panthera -> template -> push('current_lang', $_GET['language']);
                $filter['language'] = $_GET['language'];
            }
        }

        // set default language if not set
        if (!$filter['language']) {
            $activeLanguage = $this -> panthera -> locale -> getActive();
            $filter['language'] = $activeLanguage;
            $this -> panthera -> template -> push('current_lang', $activeLanguage);
        }

        // search query
        if ($_GET['query'])
            $filter['title*LIKE*'] = '%' .trim(strtolower($_GET['query'])). '%';

        // get page
        $page = intval($_GET['page']);
        $itemsCount = gallery::fetch($filter, False); // get total amount of categories

        // implement pager
        $uiPager = new uiPager('adminGalleryCategories', $itemsCount, 'adminGalleryCategories');
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString('GET', 'page={$page}', '_'). '\');');
        $limit = $uiPager -> getPageLimit();

        // get categories for current page
        $categories = gallery::fetch($filter, $limit[1], $limit[0]);

        $this -> panthera -> template -> push('category_list', $categories);
        $this -> panthera -> template -> push('languages', $languages);
        $this -> panthera -> template -> push('page', $page);

        // set titlebar
        $titlebar = new uiTitlebar(localize('Gallery', 'gallery'));

        return $this -> panthera -> template -> compile('gallery.tpl');
    }
}