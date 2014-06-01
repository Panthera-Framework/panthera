<?php
/**
 * System error pages
 *
 * @package Panthera\core\adminUI\debug\errorpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */


/**
  * Error pages pageController class
  *
  * @package Panthera\core\adminUI\debug\errorpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */
   
class errorpagesAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Test system error pages in one place', 'errorpages'
    );
    
    protected $permissions = array(
        'admin.errorpages.testing' => array('Test system error pages in one place', 'errorpages'),
    );
  
  
  
    /**
     * Check $_GET 'show' variable
     *
     * @author Mateusz Warzyński
     * @return null
     */
  
    protected function checkShowVariable()
    {
        switch ($_GET['show'])
        {
            case 'exception_debug':
                $this -> panthera -> logging -> debug = True;
                throw new Exception('This is a test of an exception page');
            break;
        
            case 'error_debug':
                $this -> panthera -> logging -> debug = True;
                trigger_error("Cannot divide by zero", E_USER_ERROR);
            break;
        
            case 'exception':
                $this -> panthera -> logging -> debug = False;
                throw new Exception('This is a test of an exception page');
            break;
        
            case 'error':
                $this -> panthera -> logging -> debug = False;
                trigger_error("This is a test of error page", E_USER_ERROR);
            break;
        
            case 'db_error':
                $e = new Exception("SQLSTATE[42000] [1044] Access denied for user '****'@'****' to database '****'");
                $this -> panthera -> db -> _triggerErrorPage($e);
            break;
            
            case 'notfound':
                pantheraCore::raiseError('notfound');
            break;
            
            case 'forbidden':
                pantheraCore::raiseError('forbidden');
            break;
        }
    }



    /**
     * Get list of error pages by type of error
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return array
     */

    protected function getPages()
    {
        $pages = array();
        
        $pages['error_debug'] = array(
            'name' => 'Error',
            'file' => getErrorPageFile('error_debug'),
            'testname' => 'error_debug',
            'visibility' => localize("Debugging")
        );
        
        $pages['exception_debug'] = array(
            'name' => 'Exception',
            'file' => getErrorPageFile('exception_debug'),
            'testname' => 'exception_debug',
            'visibility' => localize("Debugging")
        );  
        $pages['db_error'] = array(
            'name' => 'Database error',
            'file' => getErrorPageFile('db_error'),
            'testname' => 'db_error',
            'visibility' => localize("Public")
        );
        
        $pages['error'] = array(
            'name' => 'Error',
            'file' => $errorFile,
            'testname' => 'error',
            'notice' => !(bool)getErrorPageFile('error'),
            'visibility' => localize("Public")
        );
        
        $pages['exception'] = array(
            'name' => 'Exception',
            'file' => $exceptionsFile,
            'testname' => 'exception',
            'notice' => !(bool)getErrorPageFile('exception'),
            'visibility' => localize("Public")
        );
        
        // TODO: Implement 404 error pages
        $pages['notfound'] = array(
            'name' => 'Not found (404)',
            'file' => getContentDir('/templates/notfound.php'),
            'testname' => 'notfound',
            'notice' => !(bool)getErrorPageFile('notfound'),
            'visibility' => localize("Public")
        );
        
        // TODO: Implement 403 forbidden pages
        $pages['access'] = array(
            'name' => 'Forbidden (403)',
            'file' => getContentDir('/templates/forbidden.php'),
            'testname' => 'forbidden',
            'notice' => !(bool)getErrorPageFile('forbidden'),
            'visibility' => localize("Public")
        );
        
        return $pages;
    }

    /**
     * Main, display function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */
    
    public function display()
    {
        $this -> checkShowVariable();
        
        if (!getErrorPageFile('error'))
            $errorFile = '/content/templates/error.php';
        
        if (!getErrorPageFile('error'))
            $exceptionsFile = '/content/templates/exception.php';
        
        $this -> panthera -> template -> push('errorPages', $this->getPages());
        
        return $this -> panthera -> template -> compile('errorpages.tpl');
    }
}
