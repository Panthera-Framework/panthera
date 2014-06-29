<?php
class advertisementsAjaxControllerSystem extends pageController
{
    protected $permissions = 'admin';
    protected $uiTitlebar = array(
        'Adveristiments management', 'adveristiments',
    );
    
    /**
     * Edit advertisement
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function editAdAction()
    {
        $item = new adItem('adid', $_GET['adId']);
        
        if (!$item -> exists())
            panthera::raiseError('notfound');
        
        if (isset($_POST['title']) and $_POST['title'])
            $item -> name = trim($_POST['title']);
        
        if (isset($_POST['htmlcode']))
            $item -> htmlcode = $_POST['htmlcode'];
        
        if (isset($_POST['expiration']))
            $item -> expires = Tools::userFriendlyStringToDate($_POST['expiration'], 'Y-m-d H:i:s');
        
        if (isset($_POST['position']))
            $item -> position = intval($_POST['position']);
        
        if ($item -> modified())
        {
            $item -> modified = DB_TIME_NOW;
            $item -> save();
        }
        
        if ($_POST['adId'])
            ajax_exit(array(
                'status' => 'success',
            ));
        
        $this -> template -> push('adItem', $item);
        $this -> template -> display('advertisements.new.tpl');
        pa_exit();
    }
    
    /**
     * Creating new advertisement
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function newAdAction()
    {
        if (isset($_POST['title']))
        {
            try {
                adItem::create(array(
                    'name' => $_POST['title'],
                    'placename' => $_POST['placename'],
                    'htmlcode' => $_POST['htmlcode'],
                    'position' => $_POST['position'],
                    'expires' => $_POST['expiration'],
                    'authorid' => $this -> panthera -> user -> id,
                ));
                
                ajax_exit(array(
                    'status' => 'success',
                ));
                
            } catch (advertisementsModuleException $e) {
                
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize($e -> getMessage(), 'advertisements'),
                ));
            }
        }
        
        $this -> template -> push(array(
            'places' => adCategory::fetchAll(),
        ));
        
        $this -> template -> display('advertisements.new.tpl');
        pa_exit();
    }
    
    /**
     * Main function
     * 
     * $_POST['blockName'] - create a new block (category)
     * $_POST['removeBlock'] - remove a block by `placename`
     * $_GET['block'] - get block with it's items
     * 
     * @author Damian Kęska
     * @return string
     */
    
    public function display()
    {
        $this -> dispatchAction();
        
        /**
         * Create a new block
         */
        
        if (isset($_POST['blockName']))
        {
            adCategory::createBlock($_POST['blockName']);
            
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        $items = null;
        $category = null;
        
        /**
         * Display/remove block
         */
        
        if (isset($_GET['block']))
        {
            $category = new adCategory('placename', $_GET['block']);
            
            if ($category -> exists())
            {
                /**
                 * Remove block
                 */
                
                if (isset($_POST['removeBlock']))
                {
                    if ($category -> delete())
                        ajax_exit(array(
                            'status' => 'success',
                        ));
                }
                
                $items = $category -> getAds(0, 0, True);
            } else
                $category = null;
        }
        
        $categories = adCategory::fetchAll();
        
        $this -> template -> push(array(
            'categories' => $categories,
            'category' => $category,
            'items' => $items,
        ));
        
        return $this -> template -> compile('advertisements.tpl');
    }
}
