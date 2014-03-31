<?php
/**
  * Configuration tool to change values in config overlay
  *
  * @package Panthera\core\messages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
class messagesAjaxControllerSystem extends pageController
{
    protected $requirements = array(
        'quickmessages', 'admin/ui.searchbar', 'admin/ui.pager',
    );
    
    protected $uiTitlebar = array(
        'Articles, quick messages, news etc.', 'qmessages'
    );
    
    protected $language = 'english';
    
    protected $actionPermissions = array(
        'displayCategory' => array('can_qmsg_manage_all', 'can_qmsg_manage_{$category}'),
        'getMessage' => array('can_qmsg_manage_{$category}', 'can_qmsg_manage_all', 'can_qmsg_edit_{$msgid}'),
        'removeMessage' => array('can_qmsg_manage_{$category}', 'can_qmsg_manage_all', 'can_qmsg_edit_{$msgid}'),
        'editMessage' => array('can_qmsg_manage_{$category}', 'can_qmsg_manage_all', 'can_qmsg_edit_{$msgid}'),
        'removeCategory' => 'can_qmsg_manage_all',
        'newCategory' => 'can_qmsg_manage_all',
        'createNewMessage' => array('can_qmsg_manage_all', 'can_qmsg_manage_{$category}'),
    );
    
    /**
     * Create new category action
     * 
     * @return null
     */
    
    public function newCategoryAction()
    {
        if (strlen($_POST['title']) < 3 or strlen($_POST['title']) > 32)
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Category title should be 3 to 32 characters long', 'qmessages'),
            ));
        }

        $categoryName = $_POST['category_name'];

        if (!$categoryName)
            $categoryName = $_POST['title'];

        // strip out of special characters
        $categoryName = seoUrl($categoryName);
        
        $qmsg = quickCategory::create(filterInput($_POST['title'], 'quotehtml'), $_POST['description'], $categoryName);
        
        if ($qmsg -> exists())
        {
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Cannot create new category, maybe there is already another with same id', 'qmessages'),
        ));
    }
    
    /**
     * Remove category action
     * 
     * @return null
     */
    
    public function removeCategoryAction()
    {
        quickCategory::remove($_POST['category']);
        ajax_exit(array(
            'status' => 'success',
        ));
    }
    
    /**
     * Edit message action
     * 
     * @return null
     */
    
    public function editMessageAction()
    {
        $_POST = $this -> panthera -> get_filters('ajaxpages.messages.editMessage.POST', $_POST);
        $title = filterInput(trim($_POST['edit_msg_title']), 'quotehtml');
        $message = $_POST['edit_msg_content'];
        $msgid = intval($_POST['edit_msg_id']);
        $icon = filterInput($_POST['message_icon'], 'quotehtml');
        $url_id = seoUrl($_POST['edit_url_id']);
        
        $m = new quickMessage('id', $msgid);
        
        if (!$m -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'error' => localize('The message no longer exists, maybe it was deleted a moment ago', 'qmessages'),
            ));
        }

        // validate locale
        $language = $_POST['edit_language'];
        if (!$this -> panthera -> locale -> exists($language))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Selected language does not exists', 'langtool'),
            ));
        }
    
        // too short message
        if (strlen($message) < 10)
        {
            ajax_exit(array(
                'status' => 'failed',
                'error' => localize('Message content is too short!', 'qmessages'),
            ));
        }
        
        if ($_POST['mode'] == 'translate' AND $language != $m -> language)
        {
            // check if we can create a new message in this category
            $this -> checkPermissions('can_qmsg_manage_' .$m->category_name);
            
            $test = new quickMessage('url_id', $url_id);
            
            if ($test -> exists())
            {
                $url_id .= '-' .rand(1, 999);
            }
            
            quickMessage::create(
                $title, 
                $message, 
                $this -> panthera -> user -> login, 
                $this -> panthera -> user -> getName(),
                $url_id,
                $language,
                $m -> category_name,
                intval(!isset($_POST['edit_msg_hidden'])),
                $icon,
                $m -> unique
            );
            
            $w = new whereClause;
            $w -> add('AND', 'title', '=', $title);
            $w -> add('AND', 'language', '=', $language);
            $w -> add('AND', 'unique', '=', $m -> unique);
            
            $m = new quickMessage($w);
            
            if (!$m -> exists())
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Unknown error during message translation', 'qmessages'),
                ));
            }
        }
    
        $m -> icon = $icon;
    
        if (strlen($title) > 3)
            $m -> title = $title;
    
        if (strlen(strip_tags($message)) > 5)
            $m -> message = $message;
        else
            ajax_exit(array(
                'status' => 'failed',
                'error' => localize('Content is too short', 'qmessages'),
            ));
                
        if ($this -> panthera -> locale -> exists($_POST['edit_language']))
            $m -> language = $_POST['edit_language'];
    
        $m -> visibility = 0;
    
        if (isset($_POST['edit_msg_hidden']))
            $m -> visibility = 1;

        $m -> mod_author_login = $this -> panthera -> user ->login;
        $m -> mod_author_full_name = $this -> panthera -> user -> getName();
        
        if (!$url_id)
            $url_id = seoUrl($title);
        
        $m -> url_id = $url_id;
        
        $this -> panthera -> get_options('ajaxpages.messages.editMessage.object', $m);
        
        try {
            $m -> save();
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                'message' => slocalize('Catched exception while tried to save element: %s', 'messages', $e->getMessage()),
            ));
        }
        // message found, return in ajax response
        ajax_exit(array(
            'status' => 'success',
            'data' => $m -> getData(),
        ));
    }
    
    /**
     * Remove message action
     * 
     * @return null
     */
    
    public function removeMessageAction()
    {
        $m = new quickMessage('id', intval($_GET['msgid']));
        
        $this -> panthera -> get_options('ajaxpages.messages.removeMessage', array(
            'msgid' => $_GET['msgid'], 
            'category' => $_GET['category'],
        ));
        
        if (!$m -> exists() or $m -> category_name != $_GET['category'])
            $this -> checkPermissions(false);
    
        if (quickMessage::remove($_GET['msgid']))
            ajax_exit(array('status' => 'success'));
        
        ajax_exit(array(
            'status' => 'failed',
            'error' => localize('Unknown error', 'messages')
        ));
    }
    
    /**
     * Create a new message
     * 
     * @hook ajaxpages.messages.newMessage.POST $_POST
     * @return null
     */
    
    public function createNewMessageAction()
    {
        $_POST = $this -> panthera -> get_filters('ajaxpages.messages.newMessage.POST', $_POST);
        
        $title = filterInput(trim($_POST['message_title']), 'quotehtml');
        $content = $_POST['message_content'];
        $visibility = (bool)$_POST['message_hidden'];
        $categoryName = $_GET['category'];
        $icon = filterInput($_POST['message_icon'], 'quotehtml');
        $language = $this -> panthera -> locale -> getActive();

        
        // category validation
        $category = new quickCategory('category_name', $categoryName);
        
        if (!$category -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid category', 'qmessages'),
            ));
        }
        
        // set other language than active
        if ($_POST['language'] != $language)
        {
            if ($this -> panthera -> locale -> exists($_POST['language']))
                $language = $_POST['language'];
        }
        
        $url_id = seoUrl($title);
        
        $q = new quickMessage('url_id', $url_id);
        
        // if exists create a new unique name
        if ($q -> exists())
        {
            $url_id = $this -> panthera -> db -> createUniqueData('quick_messages', 'url_id', $url_id);
        }

        if(strlen($title) < 4)
            ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short', 'qmessages')));

        if (strlen($content) < 4)
            ajax_exit(array('status' => 'failed', 'message' => localize('Content is too short', 'qmessages')));

        quickMessage::create($title, $content, $this -> panthera -> user->login, $this -> panthera -> user -> getName(), $url_id, $language, $categoryName, $visibility, $icon);

        // get item details to return in ajax response (this could be useful for creating dynamic items on page)
        $item = new quickMessage('url_id', $url_id);

        $this -> panthera -> get_options('ajaxpages.messages.newMessage.createdItem', $item);

        ajax_exit(array(
            'status' => 'success',
            'data' => $item -> getData(),
        ));
    }
    
    /**
     * Get message action
     * 
     * @return null
     */
    
    public function getMessageAction()
    {
        $msgid = intval($_GET['msgid']);
        $m = new quickMessage('id', $msgid);
        
        if (!$m->exists() or $m->category_name != $_GET['category'])
        {
            // display 403/404
            $this -> checkPermissions(false);
        }
        
        $this -> panthera -> get_options('ajaxpages.messages.getMessage', $m);
        
        /*$this -> checkPermissions(array(
            'can_qmsg_manage_' .$m->category_name,
            'can_qmsg_manage_all',
            'can_qmsg_edit_' .$m->id,
        ));*/
        
        $operation = localize('Editing a message', 'custompages');
        $language = $m -> language;
        $mode = 'edit';
        
        if ($_POST['destLanguage'] and $_POST['destLanguage'] != 'undefined')
        {
            $this -> checkPermissions('can_qmsg_manage_' .$m->category_name);
            
            // check if language exists
            if (!$this -> panthera -> locale -> exists($_POST['destLanguage']))
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Selected language does not exists', 'langtool'),
                ));
            }
            
            $w = new whereClause;
            $w -> add('AND', 'unique', '=', $m->unique);
            $w -> add('AND', 'language', '=', $_POST['destLanguage']);
            
            $test = new quickMessage($w);
            
            if ($test -> exists())
            {
                $m = $test;
            }
            
            $operation = slocalize('Create a translation in %s', 'qmessages', $_POST['destLanguage']);
            $language = $_POST['destLanguage'];
            $mode = 'translate';
        }
        
        ajax_exit(array(
            'status' => 'success',
            'title' => $m->title, 
            'message' => $m->message,
            'id' => $m->id,
            'visibility' => $m->visibility,
            'icon' => $m->icon,
            'language' => $language,
            'url_id' => $m->url_id,
            'operation' => $operation,
            'unique' => $m->unique,
            'mode' => $mode,
        ));
    }
    
    /**
     * Display category action
     * 
     * @return null
     */
    
    public function displayCategoryAction()
    {
        $page = intval($_GET['page']);
        $categoryName = $_GET['category'];
        $category = new quickCategory('category_name', $categoryName);
    
        if (!$category->exists())
        {
            $this -> checkPermissions(false);
        }
        
        // searchbar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);
        $sBar -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), '#', '?display=acl&cat=admin&popup=true&name=can_see_qmsg_all,can_manage_qmsg_all', localize('Manage permissions'));
        
        $sBar -> addSetting('order', localize('Order by', 'custompages'), 'select', array(
            'title' => array('title' => localize('title', 'qmessages'), 'selected' => ($_GET['order'] == 'title')),
            'author_login' => array('title' => localize('author', 'qmessages'), 'selected' => ($_GET['order'] == 'author_login')),
        ));
        
        $sBar -> addSetting('direction', localize('Direction', 'custompages'), 'select', array(
            'ASC' => array('title' => localize('Ascending'), 'selected' => ($_GET['direction'] == 'ASC')),
            'DESC' => array('title' => localize('Descending'), 'selected' => ($_GET['direction'] == 'DESC'))
        ));
        
        $w = new whereClause();
        $w -> add('AND', 'language', '=', $this -> language, 1);
        $w -> add('AND', 'category_name', '=', $categoryName, 1);
        
        if ($sBar -> getQuery())
        {
            $w -> setGroupStatement(2, 'AND'); // adds "AND" before this group
            $w -> add('AND', 'title', 'LIKE', '%' .$sBar->getQuery(). '%', 2);
            $w -> add('OR', 'message', 'LIKE', '%' .$sBar->getQuery(). '%', 2);
            $w -> add('OR', 'author_full_name', 'LIKE', '%' .$sBar->getQuery(). '%', 2);
            $w -> add('OR', 'author_login', 'LIKE', '%' .$sBar->getQuery(). '%', 2);
            $w -> add('OR', 'unique', 'LIKE', '%' .$sBar->getQuery(). '%', 2);
        }
        
        // here we will list all messages on default page (listing)
        $count = quickMessage::fetchAll($w, False);
        
        // count pages
        $pager = new uiPager('adminQuickMessages', $count, 'adminQuickMessages', 16);
        $pager -> setLinkTemplatesFromConfig('messages.tpl');
        $pager -> maxLinks = 6;
        $limit = $pager -> getPageLimit();
        
        // get limited results
        $m = quickMessage::fetchAll($w, $limit[1], $limit[0]);
        
        // pass results to template
        $this -> panthera -> get_options('ajaxpages.messages.displayCategory.messages', $m);
        $this -> panthera -> template -> push('messages', $m);
        $this -> panthera -> template -> push('category_title',  $category -> title);
        $this -> panthera -> template -> push('category_description', $category -> description);
        $this -> panthera -> template -> push('category_name', $category -> category_name);
        $this -> panthera -> template -> push('category_id', $categoryName);
        $this -> panthera -> template -> push('languages', $this -> panthera -> locale -> getLocales());
        //$this -> panthera -> template -> push('backButtonEnabled', $this -> checkPermissions('can_qmsg_manage_all'));
        
        if ($_GET['type'] == 'ajax')
            ajax_exit(array(
                'status' => 'success',
                'response' => $m,
                'pager' => $pager->getPages($page),
                'page_from' => $limit[0],
                'page_to' => $limit[1]
            ));
        
        $this -> uiTitlebarObject = new uiTitlebar(localize('Messages category', 'qmessages'). ' - ' .localize($category->title). ' (' .slocalize('only in %s language', 'qmessages', $this->language). ')');
        $this -> uiTitlebarObject -> addIcon($this -> panthera -> template -> getStockIcon('quickMessages'), 'left');
        
        // display template
        $this -> panthera -> template -> display('messages_displaycategory.tpl');
        pa_exit();
    }
    
    /**
     * Main screen of quick messages
     * 
     * @return string
     */
    
    public function display()
    {
        // permissions variables
        $this -> panthera -> template -> push('isAdmin', checkUserPermissions($this -> panthera -> user, True));
        $this -> pushPermissionVariable('category', $_GET['category']);
        $this -> pushPermissionVariable('msgid', $_GET['msgid']);
        
        $this -> panthera -> locale -> loadDomain('qmessages');
        
        // get active locale with override if avaliable
        $this -> language = $this -> panthera -> locale -> getFromOverride($_GET['language']);
        $this -> panthera -> template -> push ('language', $this->language);
        
        $this -> dispatchAction();
        
        $this -> panthera -> template -> push('action', '');
        
        // Search bar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?display=messages&cat=admin');
        $sBar -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), '#', '?display=acl&cat=admin&popup=true&name=can_see_qmsg_all,can_manage_qmsg_all', localize('Manage permissions'));
        
        $sBar -> addSetting('order', localize('Order by', 'custompages'), 'select', array(
            'category_id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
            'category_name' => array('title' => 'name', 'selected' => ($_GET['order'] == 'category_name')),
            'title' => array('title' => 'title', 'selected' => ($_GET['order'] == 'title'))
        ));
        
        $sBar -> addSetting('direction', localize('Direction', 'custompages'), 'select', array(
            'ASC' => array('title' => localize('Ascending'), 'selected' => ($_GET['direction'] == 'ASC')),
            'DESC' => array('title' => localize('Descending'), 'selected' => ($_GET['direction'] == 'DESC'))
        ));
        
        $page = intval($_GET['page']);
        $order = 'category_id'; $orderColumns = array('category_id', 'category_name', 'title');
        $direction = 'DESC';
        
        $w = new whereClause();
        
        // order by
        if (in_array($_GET['order'], $orderColumns))
        {
            $order = $_GET['order'];
         }
                
        if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
        {
            $direction = $_GET['direction'];
        }

        if ($sBar -> getQuery())
        {
            $w -> add('AND', 'title', 'LIKE', '%' .$sBar->getQuery(). '%');
            $w -> add('OR', 'description', 'LIKE', '%' .$sBar->getQuery(). '%');
            $w -> add('OR', 'category_name', 'LIKE', '%' .$sBar->getQuery(). '%');
        }
        
        $total = quickCategory::fetchAll($w, False, False, $order, $direction);

        // Pager stuff
        $uiPager = new uiPager('quickMessages', $total, 'quickMessages', 32);
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplatesFromConfig('messages.tpl');
        $limit = $uiPager -> getPageLimit();
        
        // get all categories
        $categories = quickCategory::fetchAll($w, $limit[1], $limit[0], $order, $direction);
        $this -> panthera -> template -> push('categories', $categories);
        
        // add icon to titlebar
        $this -> uiTitlebarObject -> addIcon($this -> panthera -> template -> getStockIcon('quickMessages'), 'left');
        return $this -> panthera -> template -> compile('messages.tpl');
    }
}
