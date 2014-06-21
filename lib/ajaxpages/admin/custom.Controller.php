<?php
/**
 * Static pages - management (admin panel)
 *
 * @package Panthera\core\components\custompages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

pageController::$searchFrontControllerName = 'customAjaxControllerSystem';

/**
 * Custom pages manager
 *
 * @package Panthera\core\components\custompages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class customAjaxControllerSystem extends pageController
{
    protected $defaultAction = 'main';

    protected $uiTitlebar = array(
        'Static pages', 'custompages',
    );

    protected $actionPermissions = array(
        'main' => _CONTROLLER_PERMISSION_INLINE_,
        'removePage' => _CONTROLLER_PERMISSION_INLINE_,
        'createPage' => array(
            'custompages.management' => array('Custom pages management', 'custompages'),
            'custompages.addpage' => array('Add a new custompage', 'custompages'),
        ),
        'editPage' => _CONTROLLER_PERMISSION_INLINE_,
        'savePage' => _CONTROLLER_PERMISSION_INLINE_,
    );

    /**
     * Main function
     *
     * @return null
     */


    public function display()
    {
        $this -> dispatchAction();
    }

    /**
     * Save page action
     *
     * @feature custompages.savePage.nonexistent pid Executed when page does not exists
     * @feature custompages.savePage.save $cpage Executed on page save
     * @return null
     */

    public function savePageAction()
    {
        $cpage = new customPage('id', $_GET['pid']);

        // show 403/404 error if page not found
        if (!$cpage->exists())
        {
            $this -> getFeature('custompages.savePage.nonexistent', $_GET['pid']);
            $this -> checkPermissions(false);
        }

        $this -> checkPermissions(array(
            'custompages.edit.' .$cpage->unique,
            'custompages.edit.id.' .$cpage->id,
            'custompages.management',
            'custompages.manage.lang.' .$cpage->language,
        ));

        if (!isset($_POST['allLanguages']))
        {
            meta::remove('var', 'cp_gen_' .$cpage->unique);

        } else {
            try {
                if (!meta::get('var', 'cp_gen_' .$cpage->unique))
                    meta::create('cp_gen_' .$cpage->unique, 1, 'var', $cpage->id);
                
            } catch (Exception $e) {
                // pass
            }
        }

        // if there is title specified
        if (isset($_POST['content_title']))
        {
            $title = htmlspecialchars($_POST['content_title']);

            if (strlen($title) > 0)
                $cpage -> title = $title;
        }

        if (isset($_POST['content_description']))
            $cpage -> description = htmlspecialchars($_POST['content_description']);

        if (isset($_POST['content_image']))
        {
            if (filter_var($_POST['content_image'], FILTER_VALIDATE_URL))
                $cpage -> image = htmlspecialchars($_POST['content_image']);
        }

        if (strlen($_POST['page_content_custom']) < 10)
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Message is too short', 'custompages'),
            ));

        // last time modified by user...
        $cpage -> mod_author_name = $this -> panthera -> user -> getName();
        $cpage -> mod_author_id = $this -> panthera -> user -> id;
        $cpage -> mod_time = DB_TIME_NOW;
        $cpage -> html = $_POST['page_content_custom'];

        //$cpage -> url_id = seoUrl($cpage -> title);

        if ($cpage -> url_id != $_POST['url_id'] and $_POST['url_id'] != '')
        {
            $ppage = new customPage('url_id', $_POST['url_id']);

            if ($ppage->exists())
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('There is already other page with same SEO name', 'custompages'),
                ));

            $cpage -> url_id = seoUrl($_POST['url_id']);
        }

        $i = 0;
        $iMax = 15;
        // max of meta_tags allowed

        foreach ($_POST as $Key => $Value)
        {
            if (substr($Key, 0, 4) == "tag_")
            {
                $i++;
                $Value = filterMetaTag($Value);

                if ($Value == "")
                    continue;

                if ($i == $iMax)
                    break;

                $tags[] = $Value;
            }
        }
        
        $cpage -> meta_tags = serialize($tags);
        $cpage -> public = (bool)intval($_POST['public']);
        $cpage = $this -> getFeature('custompages.savePage.save', $cpage);
        $cpage -> save();

        ajax_exit(array(
            'status' => 'success',
        ));
    }

    /**
     * Edit page action
     *
     * @feature custompages.editPage.tags $cpage When getting list of page tags
     * @feature custompages.editPage.display $cpage When displaying page
     * @feature custompages.editPage.createTranslation $cpage After creating a page translation
     * @feature custompages.editPage.getStatement $statement On database statement creation (whereClause object)
     *
     * @return null
     */

    public function editPageAction()
    {
        $uid = $_GET['uid'];
        $language = $this -> panthera -> locale -> getActive();

        if (isset($_GET['language']))
            $language = pantheraLocale::getFromOverride($_GET['language']);

        // get page by unique
        $statement = new whereClause();
        $statement = $this -> getFeature('custompages.editPage.getStatement', $statement, $uid);
        $statement -> add ( '', 'unique', '=', $uid );
        $statement -> add ( 'AND', 'language', '=', $language );

        $cpage = new customPage($statement, $uid);

        if ($cpage -> exists())
        {
            // is author or can manage this page or can manage all pages or can view all pages (but not edit)
            $this -> checkPermissions($cpage -> getPermissions('edit'));

            // mark as read only (this should hide save button)
            if (!$this -> checkPermissions(array('custompages.edit.' .$cpage->unique, 'custompages.edit.id.' .$cpage->id, 'custompages.management'), true) and $this -> checkPermissions('custompage.viewall', true))
                $this -> panthera -> template -> push('readOnly', True);

        } else {

            /**
              * Creating pages in other languages
              *
              * @author Damian Kęska
              */

            $title = '...';
            $seoURL = substr(md5(microtime()+rand(99, 999)), 0, 4);

            // get title from custom page in other language
            $ppage = new customPage('unique', $uid);

            if (!$ppage->exists())
            {
                $this -> panthera -> logging -> output('Cannot find page by unique=' .$uid. ', displaying not found error', 'custompage');
                $this -> checkPermissions(False);
            }

            $managePermissions = $this -> checkPermissions($cpage -> getPermissions('management'), true);

            $this -> checkPermissions($cpage -> getPermissions('view'));

            // mark as read only (this should hide save button)
            if (!$managePermissions)
                $this -> panthera -> template -> push('readOnly', True);

            $title = $ppage->title;

            if ($managePermissions)
            {
                if (customPage::create($title, $language, $this -> panthera -> user -> login, $this -> panthera -> user -> id, $uid, seoUrl($seoURL)))
                {
                    $cpage = new customPage($statement, $uid);

                    if ($ppage->exists())
                    {
                        $cpage -> html = $ppage->html;
                        $cpage -> admin_tpl = $ppage->admin_tpl;
                        $cpage -> meta_tags = $ppage->meta_tags;
                        $cpage -> mod_author_name = $this -> panthera -> user -> getName();
                        $cpage -> mod_author_id = $this -> panthera -> user -> id;
                        $cpage -> mod_time = DB_TIME_NOW;
                    }

                    $cpage = $this -> getFeature('custompages.editPage.createTranslation', $cpage);
                    $cpage -> save();
                } else
                    throw new Exception('Cannot create new custom page, unknown error');
            } else {
                $this -> panthera -> logging -> output('No enough permissions to create a translation of this page', 'custompage');
            }
        }


        /**
         * This ajax subpage returns custom page's tags
         *
         * @author Damian Kęska
         */

        if ($_GET['section'] == 'tags')
        {
            if (!getUserRightAttribute($user, 'can_edit_customPages') and !getUserRightAttribute($user, 'can_manage_custompage_' . $id))
            {
                $noAccess = new uiNoAccess;
                $noAccess -> display();
            }

            $cpage = $this -> getFeature('custompages.editPage.tags', $cpage);
            $tags = @unserialize($cpage -> meta_tags);
            ajax_exit(array('tags' => $tags));
            pa_exit();
        }
        /**
          * Page editor view
          *
          * @author Damian Kęska
          */

        $html = str_replace("\n", '\\n', $cpage -> html);
        $html = str_replace("\r", '\\r', $html);
        $html = htmlspecialchars($html, ENT_QUOTES);
        
        $routeData = array(
            array(
                'title' => localize('Link with url_id', 'custompages'),
                'routeName' => 'custom_url_id',
                'params' => array('forceNative' => false, 'url_id' => $cpage -> url_id),
            ),
            
            array(
                'title' => localize('Link with unique', 'custompage'),
                'routeName' => 'custom_unique',
                'params' => array('forceNative' => false, 'unique' => $cpage -> unique),
            ),
            
            array(
                'title' => localize('Link with ID', 'custompage'),
                'routeName' => 'custom_id',
                'params' => array('forceNative' => false, 'id' => $cpage -> id),
            ),
            
            array(
                'title' => localize('Management', 'custompage'),
                'link' => '?display=custom&cat=admin&action=editPage&uid=' .$cpage->unique,
            ),
        );

        $this -> panthera -> template -> push(array(
            'customPageObject' => $cpage,
            'custompage' => $cpage -> getData(),
            'tag_list' => @unserialize($cpage -> meta_tags),
            'action' => 'edit_page',
            'languages' => $this -> panthera -> locale -> getLocales(),
            'routeData' => base64_encode(serialize($routeData)),
        ));

        if (meta::get('var', 'cp_gen_' .$cpage->unique))
        {
            $this -> panthera -> template -> push (array(
                'allLanguages' => True,
                'custompage_language' => 'all',
            ));
        } else
            $this -> panthera -> template -> push ('custompage_language', $cpage -> language);

        if ($cpage -> admin_tpl != '')
            $tpl = $cpage -> admin_tpl;

        /**
          * Customization scripts and stylesheet
          */

        $header = $cpage->meta('unique')->get('site_header');

        if (!is_array($header))
            $header = array();

        if ($cpage->meta('id')->get('site_header'))
            $header = array_merge($header, $cpage->meta('unique')->get('site_header'));

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

        $cpage = $this -> getFeature('custompages.editPage.display', $cpage);
        $this -> uiTitlebarObject -> setTitle($cpage->title." (".$cpage->language.")");
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'left');
        $this -> panthera -> template -> display('custompages_editpage.tpl');
        pa_exit();
    }

    /**
     * Create a new page
     *
     * @return null
     */

    public function createPageAction()
    {
        if (customPage::create($_POST['title'], $_POST['language'], $this -> panthera -> user))
        {
            ajax_exit(array(
                'status' => 'success',
            ));
        }

        ajax_exit(array(
            'status' => 'error',
            'message' => localize('Unknown error'),
        ));
    }

    /**
     * Remove a page
     *
     * @feature custompages.removePage $cpage On page removal try
     *
     * @param int $pid Optional page id
     * @return null
     */

    public function removePageAction($pid='')
    {
        if (!$pid)
            $pid = intval($_GET['pid']);

        $cpage = new customPage('id', $pid);

        // permissions check
        $this -> checkPermissions($cpage -> getPermissions('edit'));

        // plugins event
        $cpage = $this -> getFeature('custompages.removePage', $cpage, $uid);

        // check if custom page exists
        if ($cpage -> exists())
        {
            $data = $cpage -> getData();

            // perform a deletion
            if ($cpage -> delete())
            {
                ajax_exit(array(
                    'status' => 'success',
                    'data' => $data,
                ));
            }

            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Unknown error'),
            ));

        } else {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Page not found', 'custompages'),
            ));
        }
    }

    /**
     * Main action
     *
     * @feature custompages.main.list $tmp On pages listing
     * @feature custompages.main.queryFilter $filter When builidng query filter (searchBar)
     *
     * @return null
     */

    public function mainAction()
    {
        $rightsViewAll = getUserRightAttribute($panthera->user, 'custompage.viewall');
        $rightsManagement = getUserRightAttribute($panthera->user, 'custompages.management'); // complete management

        // searchbar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> setQuery($_GET['query']);
        $sBar -> navigate(True);
        $sBar -> addSetting('only_mine', localize('Show only my pages', 'custompages'), 'checkbox', "1");
        //$sBar -> addSetting('custom_column', localize('Search in custom column', 'custompages'), 'text', '');

        $locales = array();

        foreach ($this -> panthera -> locale -> getLocales() as $locale => $value)
        {
            if ($value == False)
                continue;

            $locales[$locale] = array(
                'title' => ucfirst($locale),
                'selected' => ($locale == $_REQUEST['lang']),
            );
        }

        // add option to show in all locales
        $locales[] = array(
            'title' => localize('all', 'custompages'),
            'selected' => (!$_GET['lang']),
        );

        $sBar -> addSetting('lang', localize('Language', 'custompages'). ' :', 'select', $locales);
        $filter = array();

        // only in selected language
        if ($_GET['lang'])
        {
            $filter['language'] = $_GET['lang'];
            $this -> panthera -> template -> push('current_lang', $_GET['lang']);
        }

        // search query
        if ($_GET['query'])
            $filter['title*LIKE*'] = '%' .trim(strtolower($_GET['query'])). '%';

        // only pages created by current user
        if (isset($_GET['only_mine']))
            $filter['author_id'] = $this -> panthera -> user -> id;

        $filter = $this -> getFeature('custompages.main.queryFilter', $filter);
        $page = intval($_GET['page']);

        if (!isset($itemsCount))
            $itemsCount = customPage::fetchAll($filter, False);

        $uiPager = new uiPager('customPages', $itemsCount, 'adminCustomPages', 128);
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplatesFromConfig('custompages.tpl');
        $limit = $uiPager -> getPageLimit();

        // if does not exists in cache, regenerate it
        if (!isset($tmp))
        {
            if (count($filter))
                $p = customPage::fetchAll($filter, $limit[1], $limit[0]);
            else
                $p = customPage::fetchAll('', $limit[1], $limit[0]);

            $tmp = array();

            foreach ($p as $page)
            {
                $languages = array(
                    $page->language => True,
                );

                // group pages in multiple languages by unique
                if (isset($tmp[$page->unique]))
                {
                    $languages = $tmp[$page->unique]['languages'];
                    $languages[$page->language] = $page -> title;
                    $tmp[$page->unique]['languages'] = $languages;

                    if (isset($tmp[$page->unique]['languages'][$this -> panthera -> locale -> getActive()]))
                    {
                        $tmp[$page->unique]['title'] = $tmp[$page->unique]['languages'][$this -> panthera -> locale -> getActive()];
                        continue;
                    }
                }
                
                $tmp[$page->unique] = array(
                    'id' => $page -> id,
                    'unique' => $page -> unique,
                    'url_id' => $page -> url_id,
                    'author_id' => $page -> author_id,
                    'modified' => $page -> mod_time,
                    'created' => $page -> created,
                    'title' => $page -> title,
                    'author_name' => $page -> author_name,
                    'mod_author_name' => $page -> mod_author_name,
                    'language' => $page -> language,
                    'languages' => $languages,
                    'managementRights' => $this -> checkPermissions($page -> getPermissions('management'), true),
                    'object' => $page,
                    'additionalInfo' => $this -> panthera -> logging -> debug ? "==== DEBUG: ID: " .$page -> id. ", Unique: " .$page -> unique. ", url_id: " .$page -> url_id : "",
                );
            }
        }

        if (count($tmp) > 0)
        {
            foreach ($tmp as $pageUnique => $page)
            {
                // dont show pages user dont have rights
                if (!$this -> checkPermissions('custompage.page.edit.' .$page['unique'], true) and $this -> checkPermissions('custompage.viewall', true))
                {
                    unset($tmp[$pageUnique]);
                    continue;
                }

                $tmp[$pageUnique]['managementRights'] = true;
            }

            $tmp = $this -> getFeature('custompages.main.list', $tmp);
            $this -> panthera -> template -> push('pages_list', $tmp);
        }

        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'left');
        $this -> panthera -> template -> push('rightsToCreate', $rightsManagement);
        $this -> panthera -> template -> push('locales', $this -> panthera -> locale -> getLocales());
        $this -> panthera -> template -> display('custompages.tpl');
        pa_exit();
    }
}