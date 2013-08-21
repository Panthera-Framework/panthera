<?php
/**
  * Lang tool library - translations editor functions
  *
  * @package Panthera\modules\liblangtool
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

global $panthera;

/**
  * Group of static methods providing locales management
  *
  * @package Panthera\modules\liblangtool
  * @author Damian Kęska
  */

class localesManagement
{
    /**
      * List avaliable translations
      *
      * @return array
      * @author Damian Kęska
      */

    public static function getLocales()
    {
        $dirs = array(PANTHERA_DIR. '/locales', SITE_DIR. '/content/locales');
        $languages = array();

        foreach ($dirs as $dir)
        {
            $files = scandir($dir);

            if (count($files) > 0)
            {
                foreach ($files as $file)
                {
                    if($file == "." or $file == "..")
                        continue;

                    if (!is_dir($dir. '/' .$file))
                        continue;

                    $languages[$file] = $dir;
                }
            }
        }

        return $languages;
    }

    /**
      * Check if translation exists
      *
      * @param string $language Language name
      * @return bool on false, and string with path on success
      * @author Damian Kęska
      */

    public static function getLocaleDir($language)
    {
        $dirs = array(PANTHERA_DIR. '/locales', SITE_DIR. '/content/locales');

        foreach ($dirs as $dir)
        {
            if (is_dir($dir. '/' .$language))
                return $dir. '/' .$language;
        }

        return False;
    }
    
    /**
      * Create a new locale
      *
      * @param string $locale
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function create($locale, $copy='')
    {
        global $panthera;
        
        if ($locale == '')
            return False;
    
        mkdir(SITE_DIR. '/content/locales/' .$locale);
        
        if (is_dir(SITE_DIR. '/content/locales/' .$locale))
        {
            if ($copy != '')
            {
                $panthera -> importModule('filesystem');
                
                if (is_dir(SITE_DIR. '/content/locales/' .$copy))   
                    recurse_copy(SITE_DIR. '/content/locales/' .$copy, SITE_DIR. '/content/locales/' .$locale);
                    
            }
            
            return True;
        }
           
        return False;
    }
    
    /**
      * Get domain directory
      *
      * @param string $language Language name
      * @param string $domain Domain name
      * @return string|bool 
      * @author Damian Kęska
      */
    
    public static function getDomainDir($language, $domain)
    {
        $dirs = array(SITE_DIR. '/content/locales/' .$language. '/' .$domain. '.phps', PANTHERA_DIR. '/locales/' .$language. '/' .$domain. '.phps');
        
        foreach ($dirs as $dir)
        {
            if (is_file($dir))
                return $dir;
        }
        
        return False;
    }

    /**
      * Get list of domains avaliable in selected $language
      *
      * @param string $language Language name
      * @return array on success or false when invalid language specified
      * @author Damian Kęska
      */

    public static function getDomains($language)
    {
        $dirs = array(PANTHERA_DIR. '/locales/' .$language, SITE_DIR. '/content/locales/' .$language);
        $list = array();

        foreach ($dirs as $dir)
        {
            if (!is_dir($dir))
                continue;

            $domains = scandir($dir);

            foreach ($domains as $domain)
            {
                $info = pathinfo($domain);

                if ($info['extension'] != 'phps')
                    continue;

                $list[] = $domain;
            }
        }

        return $list;
    }

    /**
      * Create a language domain with name $domain in $locale locale
      *
      * @param string $locale Language name
      * @param string $domain Name of the domain
      * @return bool
      * @author Damian Kęska
      */

    public static function createDomain($locale, $domain)
    {
        $dir = self::getLocaleDir($locale);

        if ($dir == False)
            throw new Exception('Cannot create domain for unknown language, please create "' .$locale. '" first', 334);

        // create dir if does not exists
        if (!is_dir(SITE_DIR. '/content/locales/' .$locale. '/'))
        {
            mkdir(SITE_DIR. '/content/locales/' .$locale. '/');
        }

        $fp = fopen(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps', 'w');
        fwrite($fp, serialize(array()));
        fclose($fp);

        if (!is_file(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps'))
            throw new Exception('Cannot write to file "' .SITE_DIR. '/content/locales/' .$domain. '.phps", please check permissions', 335);

        return True;

    }

    /**
      * Remove domain file
      *
      * @param string $locale Language
      * @param string $domain Domain name
      * @return bool
      * @author Damian Kęska
      */

    public static function removeDomain($locale, $domain)
    {
        global $panthera;
    
        if (is_file(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps'))
        {
            // clean up the cache if avaliable
            if ($panthera->cache)
                $panthera->cache->remove('locale.' .$locale. '.' .$domain);
        
            unlink(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps');
            return True;
        }

        return False;
    }

    /**
      * Rename domain (works only with /content)
      *
      * @param string $locale Language
      * @param string $domain Domain name
      * @param string $newName New domain name
      * @return bool
      * @author Damian Kęska
      */

    public static function renameDomain($locale, $domain, $newName)
    {
        global $panthera;
    
        // check if destination already exists
        if (is_file(SITE_DIR. '/content/locales/' .$locale. '/' .$newName. '.phps'))
            return False;

        if (is_file(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps'))
        {
            // clean up the cache if avaliable
            if ($panthera->cache)
                $panthera->cache->remove('locale.' .$locale. '.' .$domain);
        
            rename(SITE_DIR. '/content/locales/' .$locale. '/' .$domain. '.phps', SITE_DIR. '/content/locales/' .$locale. '/' .$newName. '.phps');
            return True;
        }

        return False;
    }

    /**
      * Remove all locale files (works only with /content)
      *
      * @param string $locale Locale name
      * @return mixed
      * @author Damian Kęska
      */

    public static function removeLocale($locale)
    {
        global $pantera;

        if ($locale == "")
            return false;

        if (is_dir(SITE_DIR. '/content/locales/' .$locale))
        {
            $panthera -> importModule('filesystem');
            deleteDirectory(SITE_DIR. '/content/locales/' .$locale);
            return true;
        }

        return false;
    }
    
    /**
      * Rename a locale
      *
      * @param string $locale
      * @param string $newName
      * @return mixed 
      * @author Damian Kęska
      */

    public static function renameLocale($locale, $newName)
    {
        if ($locale == "" or $locale == $newName)
            return False;

        if (is_dir(SITE_DIR. '/content/locales/' .$locale) and !is_dir(SITE_DIR. '/content/locales/' .$newName))
        {
            return rename(SITE_DIR. '/content/locales/' .$locale, SITE_DIR. '/content/locales/' .$newName);
        }
        
        return false;
    }

    /**
      * Get object of locale domain
      *
      * @param string name
      * @return mixed
      * @author Damian Kęska
      */

    /*public function getLocaleDomain($localeName, $domain)
    {
        $dir = self::languageExists($localeName);
        if (!$dir)
            return False;

        return new localeDomain($localeName, $domain);
    }*/
}

/**
  * Object of this class represents a translation that can be modified
  *
  * @package Panthera\modules\liblangtool
  * @author Damian Kęska
  */

class localeDomain
{
    protected $panthera; // Panthera object
    protected $dir = "";
    protected $memory = array();
    protected $locale;
    protected $domain;

    /**
      * Check there is such language domain etc.Musisz 
      *
      * @param string $localeName Locale name (language)
      * @param string $domain Domain name (file with .phps extension)
      * @throws Exception
      * @return void
      * @author Damian Kęska
      */

    public function __construct($localeName, $domain)
    {
        global $panthera;
        $this->panthera = $panthera;
        $this->locale = $localeName;
        $this->domain = $domain;

        // check if file exists in /content
        if (is_file(SITE_DIR. '/content/locales/' .$localeName. '/' .$domain. '.phps'))
            $this->dir = SITE_DIR. '/content/locales/' .$localeName. '/' .$domain. '.phps';

        // check if file exists in /lib
        if (is_file(PANTHERA_DIR. '/locales/' .$localeName. '/' .$domain. '.phps') and $this->dir == "")
            $this->dir = PANTHERA_DIR. '/locales/' .$localeName. '/' .$domain. '.phps';

        if ($this->dir != "")
            $this->memory = unserialize(file_get_contents($this->dir));
        else
            throw new Exception('404 "' .$domain. '" domain not found, looked in "' .$this->dir. '" but found nothing... :(', 404);
    }

    /**
      * Get all translated strings
      *
      * @return array
      * @author Damian Kęska
      */

    public function getStrings()
    {
        return $this->memory;
    }

    /**
      * Check if domain exists
      *
      * @return bool
      * @author Damian Kęska
      */

    public function exists()
    {
        return (bool)$this->dir;
    }

    /**
      * Translate string (modify existing or add new)
      *
      * @param string $id Original message
      * @param string $str Translation
      * @return bool Always returns true
      * @author Damian Kęska
      */

    public function setString($id, $str)
    {
        $this->memory[$id] = $str;
        return True;
    }

    /**
      * Check if original message string exists in domain
      *
      * @param string $id Original message
      * @return bool
      * @author Damian Kęska
      */

    public function stringExists($id)
    {
        return array_key_exists($id, $this->memory);
    }

    /**
      * Remove string from domain
      *
      * @param string $id Original message
      * @return bool
      * @author Damian Kęska
      */

    public function removeString($id)
    {
        if (!$this->stringExists($id))
            return False;

        unset($this->memory[$id]);
        return True;
    }

    /**
      * Get selected translation from memory
      *
      * @param string $id Original string
      * @return string|null
      * @author Damian Kęska
      */

    public function getString($id)
    {
        if ($this->stringExists($id))
        {
            return $this->memory[$id];
        }

        return Null;
    }

    /**
      * Save changes back to file (if file is from /lib it will save it to /content)
      *
      * @throws Exception
      * @param string name
      * @return mixed
      * @author Damian Kęska
      */

    public function save()
    {
        if (!$this->exists())
            return false;

        if (strpos($this->dir, PANTHERA_DIR) !== false)
            $saveDir = SITE_DIR. '/content/' .str_replace(PANTHERA_DIR, '', $this->dir);
        else
            $saveDir = $this->dir;

        @mkdir(dirname($saveDir));
        
        // clean up the cache if avaliable
        if ($this->panthera->cache)
            $this->panthera->cache->remove('locale.' .$this->locale. '.' .$this->domain);

        try {
            $fp = fopen($saveDir, 'w');
            fwrite($fp, serialize($this->memory));
            fclose($fp);
            return true;

        } catch (Exception $e) {
            throw new Exception('Cannot save file, please check permissions for ' .$this->dir, 403);
        }

        return false;
    }


}
