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

if (@$_GET['display'] == 'langtool') {

    $tpl = 'langtool.tpl';

    $panthera -> locale -> loadDomain('langtool');

    // we need to operate on langauge files, so we include some functions here
    $panthera -> importModule('liblangtool');

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
            $locale = $_GET['locale'];

            if (localesManagement::getLocaleDir($locale) == FALSE)
                ajax_exit(array('status' => 'failed', 'message' => localize('Locale does not exist')));

            if ($_GET['subaction'] == 'add_domain')
            {
                if (strlen($_GET['domain_name']) < 3)
                    ajax_exit(array('status' => 'failed', 'message' => localize('Name is too short!')));

                if (localesManagement::createDomain($locale, $_GET['domain_name']))
                    ajax_exit(array('status' => 'success', 'message' => localize('Domain has been successfully created!')));
                else
                    ajax_exit(array('status' => 'failed', 'message' => localize('Error!')));
            }

            if ($_GET['subaction'] == 'remove_domain') {
                if (strlen($_GET['domain_name']) < 3)
                    ajax_exit(array('status' => 'failed', 'message' => localize('Name of created domain is too short!')));
                else
                    $domain_name = substr($_GET['domain_name'], 0, strlen($_GET['domain_name'])-5);

                if (localesManagement::removeDomain($locale, $domain_name))
                    ajax_exit(array('status' => 'success', 'message' => localize('Domain has been successfully removed!')));
                else
                    ajax_exit(array('status' => 'failed', 'message' => localize('Error!')));
            }

            // Sorry...
            if ($_GET['subaction'] == 'rename_domain') {

                // public static function renameDomain($locale, $domain, $newName)

                $name = substr($_GET['domain_name'], 0, strlen($_GET['domain_name'])-5);
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

            $template -> push('locale', $_GET['locale']);
            $template -> push('domains', localesManagement::getDomains($_GET['locale']));
        }
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
        if (localesManagement::getLocaleDir($locale) == FALSE)
                ajax_exit(array('status' => 'failed', 'message' => localize('Locale does not exist')));

        // get domain name
        $name = str_replace('.phps', '', $_GET['domain']);
        $domain = new localeDomain($locale, $name);

        // check if domain and/or language exists
        if (!$domain -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Selected domain and/or locale does not exists')));


        // save changed string to locale file
        if ($_GET['subaction'] == 'set_string')
        {
            // check if got string is not null (may be _POST or _GET)
            if (strlen($_POST['string']) > 1)
                $string = $_POST['string'];
            elseif (strlen($_GET['string']) > 1)
                $string = $_GET['string'];
            else
                ajax_exit(array('status' => 'failed', 'message' => localize('String is empty!')));

            // check if got ID is not null (may be _POST or _GET)
            if (strlen($_POST['id']) > 1)
                $id = $_POST['id'];
            elseif (strlen($_GET['id']) > 1)
                $id = $_GET['id'];
            else
                ajax_exit(array('status' => 'failed', 'message' => localize('ID is empty!')));

            if ($domain->setString($id, $string))
            {
                $domain->save(); // save translation
                ajax_exit(array('status' => 'success', 'message' => localize('Saved'), 'string' => $string));
            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot save string, unknown error')));
            }
        }

        // remove translation from domain
        if ($_GET['subaction'] == 'remove_string')
        {


            if (!$domain->stringExists($_GET['id']))
                ajax_exit(array('status' => 'failed', 'message' => localize("String does not exist!")));

            if ($domain -> removeString($_GET['id'])) {
                $domain -> save(); // save domain without string (remove string permanently)
                ajax_exit(array('status' => 'success'));
            }

            ajax_exit(array('status' => 'failed'));
        }

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
    }

    $template -> push('locales', localesManagement::getLocales());
}

?>
