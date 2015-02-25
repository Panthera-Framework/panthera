<?php
/**
 * Ajax front controller
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska <damian@pantheraframework.org>
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

// framework
require_once 'content/app.php';

// front controllers utils
include_once PANTHERA_DIR. '/pageController.class.php';

class _ajaxControllerSystem extends pageController
{
    protected $category = '';
    protected $page = '';

    /**
     * Let any hook or module be able to modify this value
     *
     * @var bool
     */
    public $stop = false;

    /**
     * Select a controller category (sub-directory) eg. "admin" and check if selected controller is avaliable
     *
     * @input string $_GET['cat']
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    protected function selectPageAndCategory()
    {
        // if we are using ajaxpage from selected category
        if (isset($_GET['cat']) && $_GET['cat'])
        {
            $this -> category = str_replace('/', '', str_replace('.', '', $_GET['cat']));

            if (!getContentDir('ajaxpages/' .$this -> category))
                $this -> category = '';

            $this -> category .= '/';
        }

        $this -> page = $this -> category . str_replace('/', '', addslashes($_GET['display']));

        // admin category is built-in
        if ($this -> category == 'admin/')
        {
            $this -> template -> setTemplate('admin');

            // check user permissions
            if (!getUserRightAttribute($this -> user, 'admin.accesspanel'))
            {
                $this -> template -> display('no_access.tpl');
                pa_exit();
            }

            // check if requested using ajax (browser is mostly sending a header HTTP_X_REQUESTED_WITH)
            if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) and !isset($_GET['_bypass_x_requested_with']))
                pa_redirect('pa-admin.php?'.$_SERVER['QUERY_STRING']);

            // set main template
            $this -> template -> push ('username', $this -> user ->login);

            if (is_file(SITE_DIR. '/css/admin/custom/' .$this -> page. '.css'))
                $this -> template -> addStyle('{$PANTHERA_URL}/css/admin/custom/' .$this -> page. '.css');

            if (is_file(SITE_DIR. '/js/admin/custom/' .$this -> page. '.js'))
                $this -> template -> addStyle('{$PANTHERA_URL}/js/admin/custom/' .$this -> page. '.js');
        }

        return true;
    }

    /**
     * Setup template functions
     * By default it disables generating meta tags and keywords, as we are going to only display ajax content
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    protected function setupTemplate()
    {
        // dont generate meta tags and keywords, allow only adding scripts and styles
        $this -> panthera -> template -> generateMeta = False;
        $this -> panthera -> template -> generateKeywords = False;

        return true;
    }

    /**
     * Select and run a page controller
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|void
     */
    public function runController()
    {
        $tpl = 'no_page.tpl';

        // path to objective controller
        $pageFile = getContentDir('ajaxpages/' .$this -> page. '.Controller.php');

        // try structural controller if there is no objective one
        if (!$pageFile)
            $pageFile = getContentDir('ajaxpages/' .$this -> page. '.php');

        // find page and load it
        if ($pageFile)
        {
            include $pageFile;
            $name = str_replace($this -> category, '', $this -> page);

            // try to run objective controller
            $controller = pageController::getController($name);

            if ($controller)
            {
                print($controller -> run());
                pa_exit();
            }
        }

        // set default template if none selected
        if (!$this -> template -> name)
        {
            $this -> template -> setTemplate($this -> panthera -> config -> getKey('template'));
        }

        $this -> template -> display($tpl);
        pa_exit();
    }

    /**
     * Run all actions and display
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|void
     */
    public function display()
    {
        $this -> selectPageAndCategory();

        // hooks, navigation
        $this -> panthera -> add_option('page_load_ends', array('navigation', 'appendCurrentPage'));
        $this -> panthera -> get_options('ajaxpages.category', $this -> category);
        $this -> panthera -> get_options('ajax_page', False);

        $this -> setupTemplate();
        $this -> runModules();

        if ($this -> stop)
            return false;

        $this -> runController();
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, '_ajaxControllerSystem');