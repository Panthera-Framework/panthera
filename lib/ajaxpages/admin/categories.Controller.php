<?php
/**
 * Categories management
 * 
 * @package Panthera\core\components\categories
 * @author Damian Kęska
 * @license LGPLv3
 */
 
/**
 * Categories management
 * 
 * @package Panthera\core\components\categories
 * @author Damian Kęska
 */

class categoriesAjaxControllerSystem extends dataModelManagementController
{
    protected $__dataModelClass = 'category';
    protected $__baseTemplate = 'categories.tpl';
    protected $__newTemplate = 'categories.new.tpl';
    protected $__editTemplate = 'categories.new.tpl';
    protected $__defaultDisplay = 'item';
    protected $__listId = 'categoryid';
    protected $__modelIdColumn = 'categoryid';
    protected $filter = null;
    protected $uiTitlebar = array(
        'Categories management', 'categories',
    );
    
    protected $permissions = array(
        'admin.categories' => array(),
    );
    
    protected $actionPermissions = array(
        'remove' => array('admin.categories.{$categoryType}', 'admin.categories', 'admin.categories.id.{$objectGroupID}'),
        'edit' => array('admin.categories.{$categoryType}', 'admin.categories', 'admin.categories.id.{$objectGroupID}'),
    );
    
    public function datamodel_categories_itemFeature(&$decision, $item)
    {
        $decision = true;
    }
    
    /**
     * New object validation
     * 
     * @param array &$values Post values
     * @author Damian Kęska
     */
    
    public function datamodel_categories_precreateFeature(&$values)
    {
        $values['categoryType'] = $_GET['categoryType'];
        
        // edit hook can fit to creating new object validation too
        $this -> datamodel_categories_preeditFeature($values);
    }
    
    /**
     * Object edit validation
     * 
     * @param object &$object Validated object
     * @author Damian Kęska
     */
    
    public function datamodel_categories_preeditFeature(&$object)
    {
        $input = $_POST;
        $parent = new category('categoryid', $_POST['object_parentid']);
        $categoryID = null;
        
        if (is_object($object))
            $categoryID = $object -> categoryid;
        
        if ($_POST['object_parentid'] and (($categoryID && $object -> categoryid == $_POST['object_parentid']) || !$parent -> exists()))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid parent category', 'categories'),
            ));
        }
        
        if ($_POST['object_parentid'] and intval($_POST['object_priority']) > intval($parent -> priority))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => slocalize('Priority should be lower than parent category priority, please input at least %s', 'categories', ($parent -> priority - 1)),
            ));
        }
        // check parent categories for infine loop
        if ($_POST['object_parentid'] and $categoryID)
        {
            $i=0; $maxI = 50;
            
            while ($parent -> parentid)
            {
                $i++;
                $parent = new category('categoryid', $parent -> parentid);
                
                if ($parent -> categoryid == $categoryID)
                    ajax_exit(array(
                        'status' => 'failed',
                        'message' => localize('Invalid parent category', 'categories'),
                    ));
                
                if ($i >= $maxI)
                    break;
            }
        }
        
        if (!$_POST['object_title'])
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please type a title', 'categories'),
            ));
    }

    /**
     * Main function
     * 
     * @return string
     * @author Damian Kęska
     */
    
    public function display()
    {
        $this -> filter = new whereClause;
        $this -> filter -> add('AND', 'categoryType', '=', $_GET['categoryType']);
        $tree = category::fetchTree($this -> filter, 0, 0, 'priority', 'DESC');

        $this -> template -> push(array(
            'categoriesTree' => $tree,
        ));
        
        return parent::display();
    }
}
