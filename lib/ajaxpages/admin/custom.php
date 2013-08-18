<?php
/**
 * Custom pages manager
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;

// rights
if (!getUserRightAttribute($user, 'can_view_custompages') and !getUserRightAttribute($user, 'can_manage_custompage_' . $id)) {
    $template -> display('no_access.tpl');
    pa_exit();
}

// right to see pages created by other users
$rightsViewAll = getUserRightAttribute($panthera->user, 'can_see_all_custompages');
$rightsCreate = getUserRightAttribute($panthera->user, 'can_create_custompages');
$rightsManagement = getUserRightAttribute($panthera->user, 'can_manage_custompages'); // complete management

$tpl = 'custompages.tpl';

$panthera -> locale -> loadDomain('custompages');
$panthera -> locale -> loadDomain('menuedit');
$panthera -> importModule('custompages');
$panthera -> importModule('meta');


/**
  * Save custom page details
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'post_form')
{
    $cpage = new customPage('id', $_GET['pid']);
    
    // check user rights to edit custom pages or just only this custompage
    if ($cpage->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $cpage->id) and !$rightsManagement)
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
    }
    
    if (!isset($_POST['for_all_languages']))
    {
        meta::remove('var', 'cp_gen_' .$cpage->unique);
        
        if ($cpage->language != $_POST['new_language'])
            customPage::remove(array('language' => $cpage->language, 'unique' => $cpage->unique));
            
        $cpage->language = $_POST['new_language'];
    } else {
        if (!meta::get('var', 'cp_gen_' .$cpage->unique))
            meta::create('cp_gen_' .$cpage->unique, 1, 'var', $cpage->id);
    
        $cpage -> language = 'all';
        customPage::remove(array('language' => 'all', 'unique' => $cpage->unique));
    }
    
    // if there is title specified
    if (isset($_POST['content_title'])) 
    {
        $title = htmlspecialchars($_POST['content_title']);

        if (strlen($title) > 0) 
        {
            $cpage -> title = $title;
        }
    }
    
    if (strlen($_POST['page_content_custom']) < 10)
        ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short', 'custompages')));

    // last time modified by user...
    $cpage -> mod_author_name = $panthera -> user -> getName();
    $cpage -> mod_author_id = $panthera -> user -> id;
    $cpage -> mod_time = 'NOW()';
    $cpage -> html = $_POST['page_content_custom'];
    
    //$cpage -> url_id = seoUrl($cpage -> title);
    
    if ($cpage -> url_id != $_POST['url_id'] and $_POST['url_id'] != '')
    {
        $ppage = new customPage('url_id', $_POST['url_id']);
        
        if ($ppage->exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('There is already other page with same SEO name', 'custompages')));
    
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
    $cpage -> save();

    ajax_exit(array('status' => 'success', 'message' => localize('Saved', 'custompages')));
}

/**
  * Editor view
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == "edit_page") 
{
    $tpl = 'custompages_editpage.tpl';

    $uid = $_GET['uid'];
    $language = null;

    if (isset($_GET['language']))
    {
        if (array_key_exists($_GET['language'], $panthera->locale->getLocales()))
            $language = $_GET['language'];
    } 
    
    if ($language == null)
        $language = $panthera->locale->getActive();
    
    // get page by unique
    $statement = new whereClause();
    $statement -> add ( '', 'unique', '=', $uid );
    $statement -> add ( 'AND', 'language', '=', $language );
    
    $cpage = new customPage($statement, $uid);
    
    if ($cpage -> exists())
    {
        // is author or can manage this page or can manage all pages or can view all pages (but not edit)
        if ($cpage->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $cpage->id) and !$rightsManagement and !$rightsViewAll)
        {
            $template -> display('no_access.tpl');
            pa_exit();
        }
    }
    
    // mark as read only (this should hide save button)
    if (!($cpage->author_id == $panthera->user->id or getUserRightAttribute($user, 'can_manage_custompage_' . $cpage->id) or $rightsManagement) and $rightsViewAll)
    {
        $panthera -> template -> push('readOnly', True);
    }

    /**
      * Creating pages in other languages
      *
      * @author Damian Kęska
      */

    if (!$cpage -> exists()) 
    {
        $title = '...';
        $seoURL = md5(microtime());
        
        // get title from custom page in other language
        $ppage = new customPage('unique', $uid);

        if ($ppage->exists())
        {
            $title = $ppage->title;
            
            // (is owner or can manage this page and can create new pages) or (just can manage all pages)
            if (($ppage->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $ppage->id and $rightsCreate)) or !$rightsManagement)
            {
                $template -> display('no_access.tpl');
                pa_exit();
            }
        }
        
        if (customPage::create($title, $language, $panthera -> user -> login, $panthera -> user -> id, $uid, seoUrl($seoURL)))
        {
            $cpage = new customPage($statement, $uid);
            
            if ($ppage->exists())
            {
                $cpage -> html = $ppage->html;
                $cpage -> admin_tpl = $ppage->admin_tpl;
                $cpage -> meta_tags = $ppage->meta_tags;
                $cpage -> mod_author_name = $panthera -> user -> getName();
                $cpage -> mod_author_id = $panthera -> user -> id;
                $cpage -> mod_author_id = $panthera -> user -> id;
                $cpage -> mod_time = 'NOW()';
            }
            
            $cpage -> save();
        } else
            throw new Exception('Cannot create new custom page, unknown error');
    }
        
    
    /**
      * This ajax subpage returns custom page's tags
      *
      * @author Damian Kęska
      */
    
    if ($_GET['section'] == 'tags') 
    {
        if (!getUserRightAttribute($user, 'can_edit_customPages') and !getUserRightAttribute($user, 'can_manage_custompage_' . $id))
            ajax_exit(array('status' => 'failed', 'message' => localize('You dont have rights to edit this page', 'messages')));

        $tags = @unserialize($cpage -> meta_tags);
        print(json_encode(array('tags' => $tags)));
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

    $template -> push('custompage_title', $cpage -> title);
    $template -> push('custompage_title_escaped', addslashes($cpage -> title));
    $template -> push('custompage_url_id', $cpage -> url_id);
    $template -> push('custompage_unique', $cpage -> unique);
    $template -> push('custompage_id', $cpage -> id);
    $template -> push('custompage_author_name', $cpage -> author_name);
    $template -> push('custompage_author_id', $cpage -> author_id);
    $template -> push('custompage_created', $cpage -> created);
    $template -> push('custompage_modified', $cpage -> mod_time);
    $template -> push('custompage_mod_author', $cpage -> mod_author_name);
    $template -> push('custompage_mod_author_id', $cpage -> mod_author_id);
    $template -> push('custompage_html', $html);
    $template -> push('tag_list', @unserialize($cpage -> meta_tags));
    $template -> push('action', 'edit_page');
    $template -> push('languages', $panthera -> locale -> getLocales());
    
    $url = $panthera -> config -> getKey('custompage', array('url_id' => 'custom,{$id}.html', 'unique' => 'custom.{$id}.html', 'id' => 'custom-{$id}.html'), 'array');
    
    if ($url['url_id'])
        $template -> push ('custompage_url_id_address', pantheraUrl('{$PANTHERA_URL}/').str_replace('{$id}', $cpage->url_id, $url['url_id']));
        
    if ($url['unique'])
        $template -> push ('custompage_unique_address', pantheraUrl('{$PANTHERA_URL}/').str_replace('{$id}', $cpage->unique, $url['unique']));
        
    if ($url['id'])
        $template -> push ('custompage_id_address', pantheraUrl('{$PANTHERA_URL}/').str_replace('{$id}', $cpage->id, $url['id']));
    
    if (meta::get('var', 'cp_gen_' .$cpage->unique))
    {
        $template -> push ('allPages', True);
        $template -> push ('custompage_language', 'all');
    } else
        $template -> push ('custompage_language', $cpage -> language);

    if ($cpage -> admin_tpl != '')
        $tpl = $cpage -> admin_tpl;
        
    /**
      * Customization scripts and stylesheet
      */
        
    $header = $cpage->meta('unique')->get('site_header');
    
    if ($cpage->meta('id')->get('site_header') != null)
        $header = array_merge($header, $cpage->meta('unique')->get('site_header'));
        
    if (count($header) > 0)
    {
        if (count($header['scripts']) > 0)
        {
            foreach ($header['scripts'] as $key => $value)
                $panthera -> template -> addScript($value);
        }
        
        if (count($header['styles']) > 0)
        {
            foreach ($header['styles'] as $key => $value)
                $panthera -> template -> addStyle($value);
        }
    }
    
    $template -> display($tpl);
    pa_exit();
    
} elseif (@$_GET['action'] == "add_page") {

    if (!$rightsCreate and !$rightsManagement)
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
    }

    if (customPage::create($_POST['title'], $_POST['language'], $user -> login, $user -> id, md5(rand(666, 6666)), seoUrl($_POST['title'])))
        ajax_exit(array('status' => 'success', 'message' => localize('Page has been successfuly added!')));
    else
        ajax_exit(array('status' => 'error', 'message' => localize('Error! Cannot add custom page!')));

/**
  * Removing a custom page
  *
  * @author Mateusz Warzyński
  */

}

if (@$_GET['action'] == "delete_page")
{
    $pid = intval($_GET['pid']);
    $cpage = new customPage('id', $pid);
    
    // check if custom page exists
    if ($cpage -> exists()) {
    
        // if user has no rights to delete page
        if ($cpage->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $cpage->id) and !$rightsManagement)
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
        }
    
        // perform a deletion
        if (customPage::removeById($cpage -> id))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'error'));
            
    } else {
        ajax_exit(array('status' => 'error'));
    }
    
}

/**
  * List of all custom pages
  *
  * @param string name
  * @return mixed 
  * @author Damian Kęska
  */
  
$panthera -> importModule('admin/ui.searchbar');

$sBar = new uiSearchbar('uiTop');
//$sBar -> setMethod('POST');
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=custom&cat=admin&mode=search');
$sBar -> navigate(True);
$sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_view_custompages', localize('Manage permissions'));
$sBar -> addSetting('only_mine', localize('Show only my pages', 'custompages'), 'checkbox', "1");
//$sBar -> addSetting('custom_column', localize('Search in custom column', 'custompages'), 'text', '');

$locales = array();

foreach ($panthera->locale->getLocales() as $locale => $value)
{
    if ($value == False)
        continue;
        
    $locales[$locale] = array('title' => ucfirst($locale), 'selected' => ($locale == $_GET['lang'] or $locale == $_POST['lang']));
}

if (!$_GET['lang'])
    $locales[''] = array('title' => localize('all', 'custompages'), 'selected' => True);
else
    $locales[''] = array('title' => localize('all', 'custompages'), 'selected' => False);

$sBar -> addSetting('lang', localize('Language', 'custompages'). ' :', 'select', $locales);

$filter = array();

// only in selected language
if ($_GET['lang']) 
{
    $filter['language'] = $_GET['lang'];
    $template -> push('current_lang', $_GET['lang']);
}

// search query
if ($_GET['query'])
{
    $filter['title*LIKE*'] = '%' .$_GET['query']. '%';
}

// only pages created by current user
if (isset($_GET['only_mine']))
{
    $filter['author_id'] = $panthera -> user -> id;
}

if (count($filter))
    $p = customPage::fetch($filter);
else
    $p = customPage::fetch();

if (count($p) > 0) 
{
    $array = array();

    foreach ($p as $page) 
    {
        // dont show pages user dont have rights
        if (!$rightsViewAll and $page->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $page->id) and !$rightsManagement)
            continue;
    
        $languages = array($page->language => True);
    
        if (isset($array[$page->unique]))
        {
            $languages = $array[$page->unique]['languages'];
            $languages[$page->language] = True;
        }
    
        $array[$page->unique] = array(
            'id' => $page -> id, 
            'unique' => $page -> unique, 
            'url_id' => $page -> url_id, 
            'modified' => $page -> mod_time, 
            'created' => $page -> created, 
            'title' => $page -> title, 
            'author_name' => $page -> author_name, 
            'mod_author_name' => $page -> mod_author_name, 
            'language' => $page -> language, 
            'languages' => $languages, 
            'managementRights' => !($page->author_id != $panthera->user->id and !getUserRightAttribute($user, 'can_manage_custompage_' . $page->id) and !$rightsManagement)
        );
    }
    
    $template -> push('pages_list', $array);
}

$panthera -> template -> push('rightsToCreate', ($rightsCreate or $rightsManagement)); 
$panthera -> template -> push('locales', $panthera -> locale -> getLocales());
$panthera -> template -> display($tpl);
pa_exit();
