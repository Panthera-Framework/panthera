<?php
/**
 * Custom pages example action handler
 *
 * @package Panthera\core\components\custompages
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Custom pages front controller
 *
 * @package Panthera\core\components\custompages
 * @author Damian Kęska
 */

class customControllerSystem extends pageController
{
    protected $mode = 'fallback';
    protected $cpage = null;
    protected $requirements = array(
        'custompages',
    ); // list of required modules

    /**
     * Constructor
     *
     * @return object
     */

    public function __construct()
    {
        parent::__construct();
        $this -> setMode();
        $this -> buildObject();
        $this -> checkExists();
    }

    /**
     * Set language selection mode
     *
     * @input $_GET['forceNativeLanguage']
     * @return null
     */

    public function setMode()
    {
        if (isset($_GET['forceNativeLanguage']))
            $this -> mode = 'forceNative';
    }

    /**
     * Try to build object
     *
     * @return null
     */

    public function buildObject()
    {
        if (isset($_GET['url_id']))
        {
            $this -> cpage = customPage::getBy('url_id', $_GET['url_id'], '', $this -> mode);

        } elseif(isset($_GET['id'])) {
            $this -> cpage = new customPage('id', $_GET['id']);

        } elseif(isset($_GET['unique'])) {
            $this -> cpage = customPage::getBy('unique', $_GET['unique'], '', $this -> mode);
        }
    }

    /**
     * Check if page exists in database
     *
     * @return null
     */

    public function checkExists()
    {
        $panthera = pantheraCore::getInstance();

        if (!$this -> cpage or !$this -> cpage->exists())
        {
            pa_redirect($panthera -> config -> getKey('err404.url', '?404', 'string', 'errors'));
        }
    }

    /**
     * Display
     *
     * @return null
     */

    public function display()
    {
        $panthera = pantheraCore::getInstance();

        $tags = unserialize($this -> cpage -> meta_tags);
        $panthera -> template -> putKeywords($tags);
        $panthera -> template -> setTitle($this -> cpage -> title);

        if ($this -> cpage -> description)
            $panthera -> template -> addMetaTag('description', str_replace("\n", ' ', strip_tags($this -> cpage -> description)));

        // add facebook og:image tag, property type
        if ($this -> cpage -> image)
            $panthera -> template -> addMetaTag('og:image', $this -> cpage -> image, True);

        $panthera -> template -> push('custompage', $this -> cpage -> getData());
        $panthera -> template -> display('custom.tpl');
        pa_exit();
    }
}