<?php
/**
  * Configuration tool to manage translations
  *
  * @package Panthera\core\ajaxpages
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA')) 
    exit;
    
/**
  * Update missing strings cache
  *
  * @package Panthera\core\ajaxpages
  * @return array
  * @author Damian Kęska
  */
    
function updateMissingStringsCache($locale)
{
    global $panthera;

    $missingStrings = array_merge(
        localesManagement::scanForMissingStrings(PANTHERA_DIR, $locale), 
        localesManagement::scanForMissingStrings(SITE_DIR. '/content/templates', $locale),
        localesManagement::scanForMissingStrings(SITE_DIR. '/content/ajaxpages', $locale),
        localesManagement::scanForMissingStrings(SITE_DIR. '/content/pages', $locale),
        localesManagement::scanForMissingStrings(SITE_DIR. '/content/plugins', $locale),
        localesManagement::scanForMissingStrings(SITE_DIR. '/content/frontpages', $locale)
    );
                
    $panthera -> logging -> output('Creating new missing strings cache in "langtool.scan.missing.' .$locale. '"', 'langtool');
    $panthera -> varCache -> remove('langtool.scan.missing.' .$locale);
    $panthera -> varCache -> set('langtool.scan.missing.' .$locale, $missingStrings, 360);
    
    return $missingStrings;
}

if (@$_GET['display'] == 'langtool') 
{

    $tpl = 'langtool.tpl';

    $panthera -> locale -> loadDomain('langtool');

    // we need to operate on langauge files, so we include some functions here
    $panthera -> importModule('liblangtool');
    
    /**
      * Refreshing list of missing templates for current locale
      *
      * @author Damian Kęska
      */
    
    if ($_GET['action'] == 'domains' or $_GET['action'] == 'view_domain')
    {
        $locale = $_GET['locale'];

        if (!localesManagement::getLocaleDir($locale))
            ajax_exit(array('status' => 'failed', 'message' => localize('Locale does not exist')));
            
        if ($panthera->varCache)
        {
            if (!$panthera->varCache->exists('langtool.scan.missing.' .$locale))
            {
                $missingStrings = updateMissingStringsCache($locale);
            } else {
                $missingStrings = $panthera -> varCache -> get('langtool.scan.missing.' .$locale);
            }
        }
        
        if ($_GET['action'] == 'domains')
        {
            $panthera -> template -> push('missingTranslations', $missingStrings);
        } else {
            if (isset($missingStrings[$_GET['domain']]))
            {
                $panthera -> template -> push ('missingTranslations', $missingStrings[$_GET['domain']]);
            }
        }
    }

    /**
      * Domains management
      *
      * @author Mateusz Warzyński
      */

    if ($_GET['action'] == 'domains')
    {
        $tpl = 'langtool_domains.tpl';

        if (isset($_GET['locale']))
        {
            // setting correct icon   
            $icon = pantheraUrl('{$PANTHERA_URL}/images/admin/flags/unknown.png');
                
            if (is_file(SITE_DIR. '/images/admin/flags/' .$_GET['locale']. '.png'))
            {
                $icon = pantheraUrl('{$PANTHERA_URL}/images/admin/flags/' .$_GET['locale']. '.png');
            }
            
            $panthera -> template -> push ('flag', $icon);

            if ($_GET['subaction'] == 'add_domain')
            {
                if (strlen($_GET['domain_name']) < 3)
                    ajax_exit(array('status' => 'failed', 'message' => localize('Domain name is too short', 'langtool')));

                if (localesManagement::createDomain($locale, $_GET['domain_name']))
                    ajax_exit(array('status' => 'success', 'message' => localize('Done')));
                
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot create domain, please check write permissions on content/locales directory and it\'s subdirectories', 'langtool')));
            }

            if ($_GET['subaction'] == 'remove_domain') 
            {
                if (strlen($_GET['domain_name']) < 3)
                    ajax_exit(array('status' => 'failed', 'message' => localize('Name of created domain is too short', 'langtool')));
                else
                    $domain_name = str_ireplace('.phps', '', $_GET['domain_name']);

                if (localesManagement::removeDomain($locale, $domain_name))
                    ajax_exit(array('status' => 'success', 'message' => localize('Done', 'langtool')));
                
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove domain, please check write permissions on content/locales directory and it\'s subdirectories', 'langtool')));
            }

            if ($_GET['subaction'] == 'rename_domain') 
            {
                // public static function renameDomain($locale, $domain, $newName)

                $name = str_ireplace('.phps', '', $_GET['domain_name']);
                $newName = $_GET['new_domain_name'];

                // check if new name of domain is not empty and has at least 3 letters
                if (strlen($newName) > 2)
                {
                   // rename domain
                   if (localesManagement::renameDomain($locale, $name, $newName)) {
                        ajax_exit(array('status' => 'success', 'message' => localize('Domain has been renamed!')));
                   } else {
                        ajax_exit(array('status' => 'failed', 'message' => localize('Error while renaming domain!')));
                   }

                } else {
                   ajax_exit(array('status' => 'failed', 'message' => localize('New name of domain is too short or empty!')));
                }
            }
            
            $sBar = new uiSearchbar('uiTop');
            $sBar -> setQuery($_GET['query']);
            $sBar -> setAddress('?display=langtool&cat=admin&action=domains&locale='.$locale);
            $sBar -> navigate(True);
            
            $domains = localesManagement::getDomains($_GET['locale']);
            sort($domains);
            
            foreach ($domains as $key => $domain)
            {
                $dir = localesManagement::getDomainDir($_GET['locale'], str_replace('.phps', '', $domain));
                
                if (substr($dir, 0, strlen(PANTHERA_DIR)) == PANTHERA_DIR and defined('LANGTOOL_DISABLE_LIB'))
                {
                    unset($domains[$key]);
                    continue;
                }
                    
                $domains[$key] = str_ireplace('.phps', '', $domain);
            }
            
            // search loop
            if ($_GET['query'] != '') 
            {
                foreach ($domains as $key => $domain)
                {
                    if (!strstr($domains[$key], strtolower($_GET['query'])))
                        unset($domains[$key]);
                }
            }
            

            $template -> push('locale', $_GET['locale']);
            $template -> push('domains', $domains);
			
			$titlebar = new uiTitlebar(localize('Manage domains', 'langtool'));
			$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/langtool.png', 'left');
			
			$template -> display($tpl);
			pa_exit();
        }
    }
    
    /**
      * Creating a new language
      *
      * @author Damian Kęska
      */
    
    if ($_GET['action'] == 'createNewLanguage')
    {
        if (strlen($_POST['languageName']) < 3)
            ajax_exit(array('status' => 'failed', 'message' => localize('Name is too short', 'langtool')));
    
        if (!preg_match("/^([a-z]+)$/u", $_POST['languageName']))
            ajax_exit(array('status' => 'failed', 'message' => localize('Name must be only single word from a-z characters range', 'langtool')));
    
        if (localesManagement::getLocaleDir($_POST['languageName']) == True)
            ajax_exit(array('status' => 'failed', 'message' => localize('Langauge already exists', 'langtool')));
            
        localesManagement::create($_POST['languageName'], $panthera->locale->getActive());
        ajax_exit(array('status' => 'success'));
        
        
        
        
        
    /**
      * Save multiple strings
      *
      * @author Damian Kęska
      */
        
    } elseif ($_GET['action'] == 'saveStrings') {
        $data = json_decode(base64_decode($_POST['data']), true);
        $languages = array (); // here we will store objects of languages and domains
        $set = 0;
        
        foreach ($data as $form => $string)
        {
            parse_str($string, $data[$form]);
            
            // alias for $data[$form]
            $postData = $data[$form];
            
            if (!$languages[$postData['language']])
            {
                $languages[$postData['language']] = array();
            }
            
            // detect base64 encoded original string and decode it (encoded string is more easier to transport and display in HTML code when it contains quotes etc.)
            if (isset($postData['originalEncoding']))
            {
                if ($postData['originalEncoding'] == 'base64')
                {
                    $postData['original'] = base64_decode($postData['original']);
                    unset($postData['originalEncoding']);
                }
            }
            
            // skip invalid empty strings
            if (!$postData['original'] or !$postData['language'] or !$postData['translation'])
            {
                continue;
            }
            
            // create new domain object
            if (!$languages[ $postData['language'] ][ $postData['domain'] ])
            {
                try {
                    $languages[$postData['language']][$postData['domain']] = new localeDomain($postData['language'], $postData['domain']);
                } catch (Exception $e) {
                
                    if (isset($_GET['createMissingDomains']))
                    {
                        if (!localesManagement::createDomain($locale, $postData['domain']))
                        {
                            continue;
                        }
                        
                    } else {
                        $panthera -> logging -> output('Cannot find "' .$postData['domain']. '" domain for "' .$postData['language']. '" language, skipping', 'langtool');
                        continue;
                    }
                }
            }
            
            // check if domain exists
            if (!$languages[$postData['language']][$postData['domain']] -> exists())
            {
                $panthera -> logging -> output('Cannot find "' .$postData['domain']. '" domain for "' .$postData['language']. '" language, skipping', 'langtool');
                continue;
            }
            
            if (localize($postData['original'], $postData['domain']) != $postData['translation'])
            {
                $panthera -> logging -> output('Translating string "' .trim($postData['original']). '" => "' .trim($postData['translation']). '" (' .$postData['language']. '::' .$postData['domain']. ')', 'langtool');
                $languages[$postData['language']][$postData['domain']] -> setString(trim($postData['original']), trim($postData['translation']));
                $set++;
            }
        }
        
        // save all opened domains
        foreach ($languages as $langName => $lang)
        {
            foreach ($lang as $domain)
            {
                $domain -> save();
                unset($domain);
            }
        }
        
        ajax_exit(array('status' => 'success', 'message' => slocalize('Saved %s translation strings', 'langtool', $set)));
    }
   




    /**
      * Domain view
      *
      * @author Mateusz Warzyński
      */

    if ($_GET['action'] == 'view_domain')
    {
        $tpl = 'langtool_viewdomain.tpl';

        $locale = $_GET['locale'];

        // check if locale exists
        if (!localesManagement::getLocaleDir($locale))
            ajax_exit(array('status' => 'failed', 'message' => localize('Locale does not exist', 'langtool')));
            
        // get domain name
        $name = str_replace('.phps', '', $_GET['domain']);
        $domain = new localeDomain($locale, $name);

        // check if domain and/or language exists
        if (!$domain -> exists())
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Selected domain and/or locale does not exists', 'langtool')));
        }            
            
        /**
          * Adding new string
          *
          * @author Mateusz Warzyński
          */
            
        // save changed string to locale file
        if ($_GET['subaction'] == 'addNewString')
        {
            // check if got string is not null (may be _POST or _GET)
            $string = trim($_POST['string']);
            $id = trim($_POST['id']);
            
            if (!$string)
            { 
                ajax_exit(array('status' => 'failed', 'message' => localize('Translation string cannot be empty', 'langtool')));
            }
            
            // check if original string is not empty
            if (!$id)
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('Original string is empty', 'langtool')));
            }

            if ($domain->setString($id, $string))
            {
                $domain->save(); // save translation
                ajax_exit(array('status' => 'success', 'message' => localize('Saved'), 'translation' => $string, 'original' => $id, 'domain' => $name, 'language' => $locale, 'random' => rand(999, 9999)));
            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot save string, unknown error', 'langtool')));
            }
        }
        
        
        /**
          * Removing a string
          *
          * @author Mateusz Warzyński
          */

        // remove translation from domain
        if ($_GET['subaction'] == 'remove_string')
        {
            if (!$domain->stringExists($_GET['id']))
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find original string', 'langtool')));

            if ($domain -> removeString($_GET['id'])) {
                $domain -> save(); // save domain without string (remove string permanently)
                ajax_exit(array('status' => 'success'));
            }

            ajax_exit(array('status' => 'failed'));
        }
        
        // setting correct icon   
        $icon = pantheraUrl('{$PANTHERA_URL}/images/admin/flags/unknown.png');
                
        if (is_file(SITE_DIR. '/images/admin/flags/' .$_GET['locale']. '.png'))
        {
             $icon = pantheraUrl('{$PANTHERA_URL}/images/admin/flags/' .$_GET['locale']. '.png');
        }
        
        $panthera -> template -> push ('flag', $icon);

        // send data to template
        $template -> push('locale', $locale);
        $template -> push('domain', $_GET['domain']);

        // get translated string in other languages
        $translates = array();

        // get locales
        $locales = localesManagement::getLocales();

        // get translations from domain (all available languages)
        foreach ($domain->getStrings() as $id => $string)
        {
                // get translation from active language
                $translates[$id][$locale] = $string;

                $i = 0;

                // get translations from other languages
                foreach ($locales as $lang => $path)
                {
                    // if we are checking active locale - continue
                    if ($lang == $locale)
                        continue;

                    if ($i == 3) // max 3 other languages
                        break;


                    // check if domain exists
                    if (in_array($name . '.phps', localesManagement::getDomains($lang)))
                    {
                        $d = new localeDomain($lang, $name);
                    } else {
                        continue;
                    }

                    // check if translation exists
                    if ($d -> stringExists($id))
                    {
                        $translates[$id][$lang] = $d -> getString($id);
                    } else {
                        $translates[$id][$lang] = ""; // if not exists display none (maybe someone will add it)
                    }

                    $i++;
                }
        }
        
        $i = null; // clean memory

        // send data to template
        $template -> push('translates', $translates);
		
		$titlebar = new uiTitlebar(localize('Translates for', 'langtool')." ".$_GET['domain']);
		$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/langtool.png', 'left');
		
		$template -> display($tpl);
		pa_exit();
    }

    $locales = array();
    
    foreach (localesManagement::getLocales() as $name => $dir)
    {
        if (is_file(SITE_DIR. '/images/admin/flags/' .$name. '.png'))
        {
            $locales[$name] = array('icon' => pantheraUrl('{$PANTHERA_URL}/images/admin/flags/' .$name. '.png'), 'place' => $dir);
            continue;
        }
        
        $locales[$name] = array('icon' => pantheraUrl('{$PANTHERA_URL}/images/admin/flags/unknown.png'), 'place' => $dir);
    }
    
    $template -> push('locales', $locales);
	
	$titlebar = new uiTitlebar(localize('Manage languages', 'langtool'));
	$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/langtool.png', 'left');
}

?>
