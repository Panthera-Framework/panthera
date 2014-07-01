<?php
/**
 * WYSIWYG editor drafts
 *
 * @package Panthera\core\components\wysiwyg
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
    exit;

/**
 * WYSIWYG editor drafts controller
 *
 * @package Panthera\core\components\wysiwyg
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

class editor_draftsAjaxControllerSystem extends pageController
{
    protected $requirements = array(
        'editordrafts',
    );
    
    /**
     * Construct draft object from $_GET['id'] and check user permissions to edit it
     * 
     * @return object|bool
     */
    
    protected function getDraft()
    {
        if (!isset($_GET['id']))
            return false;
        
        $draft = new editorDraft('id', $_GET['id']);
        
        if (($draft -> author_id != $this -> panthera -> user -> id))
            $this -> checkPermissions('editor.drafts.management');
        
        return $draft;
    }
    
    /**
     * Save a draft
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function saveDraftAction()
    {
        $draft = $this -> getDraft();
        
        if (!$draft)
            panthera::raiseError('notfound');
        
        // update existing draft when id is provided
        editorDraft::createDraft($_POST['content'], $this -> panthera -> user -> id, $draft -> id);
        
        ajax_exit(array(
            'status' => 'success',
        ));
    }
    
    /**
     * Remove a draft
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function removeDraftAction()
    {
        $draft = $this -> getDraft();
        
        if (!$draft)
            panthera::raiseError('notfound');
        
        if ($draft -> exists())
        {
            $draft -> delete();
            
            ajax_exit(array(
                'status' => 'success',
            ));
        }
    }
    
    /**
     * View draft
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function viewDraftAction()
    {
        $draft = $this -> getDraft();
        
        if (!$draft)
            panthera::raiseError('notfound');
        
        $u = new pantheraUser('id', $draft->author_id);
        $author = $u -> getName();
        
        $this -> template -> push(array(
            'callback' => $_GET['callback'],
            'content' => filterInput($draft -> content, 'wysiwyg'),
            'date' => $draft -> date,
            'author' => $author,
            'draftID' => $draft -> id,
        ));
        
        $this -> template -> display('editor.drafts.edit.tpl');
        pa_exit();
    }
    
    /**
     * Main function
     * 
     * @return null
     */
    
    public function display()
    {
        // titlebar
        $titlebar = new uiTitlebar(localize('Saved drafts', 'editordrafts'));
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');
        
        $this -> dispatchAction();
        $count = editorDraft::fetchByUser($this -> panthera -> user -> id, '', False, False);
        
        $uiPager = new uiPager('adminEditorDrafts', $count);
        $uiPager -> setActive(intval($_GET['page']));
        $uiPager -> setLinkTemplatesFromConfig('editor_drafts.tpl');
        $limit = $uiPager -> getPageLimit();
        
        $drafts = array();
        
        foreach (editorDraft::fetchByUser($this -> panthera -> user -> id, '', $limit[1], $limit[0]) as $draft)
        {
            $draft -> readOnly(True);
            $draft -> author_id = $this -> panthera -> user -> getName();
            $drafts[$draft -> id] = $draft;
        }
        
        $this -> template -> push(array(
            'callback' => $_GET['callback'],
            'drafts' => $drafts,
        ));
        
        $this -> template -> display('editor.drafts.tpl');
        pa_exit();
    }
}

