<?php
/**
  * Read server log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Server log parser pageController 
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class accessparserAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Site traffic browser', 'accessparser'
    );
    
    protected $requirements = array(
        'admin/ui.pager',
    );
    
    protected $permissions = 'admin.accessparser';



    /**
     * Save path to server log
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */

    public function savePathAction()
    {
        if (!strlen($_POST['path']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Path cannot be empty!', 'accessparser')));
        
        $this -> panthera -> config -> setKey('path_to_server_log', $_POST['path'], 'string');
        
        ajax_exit(array('status' => 'success'));
    }


    
    /**
     * Main, display template function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */
     
    public function display()
    {
        // get translates
        $this -> panthera -> locale -> loadDomain('accessparser');
        
        $this -> dispatchAction();
        
        $parser = new accessParser;
        
        try {
            $lines = $parser->readLog();
        } catch (Exception $e) {
            $this -> panthera -> template -> push("error", true);
            $this -> panthera -> template -> push("error_message", localize($e->getMessage(), 'accessparser'));            
        }
        
        $page = $_GET['page'];
        
        $uiPager = new uiPager('accessParserLines', count($lines), 'accessParserLines', 100);
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString('GET', 'page={$page}', '_'). '\');');
        $limit = $uiPager->getPageLimit();
        
        $results = array();
        
        if (is_array($lines))
            $results = array_slice($lines, $limit[0], $limit[1]);
        
        $this -> panthera -> template -> push('lines', $results);
        $this -> panthera -> template -> push('path', $this->panthera->config->getKey('path_to_server_log'));
        
        return $this -> panthera -> template -> compile('accessparser.tpl');
    }

}  