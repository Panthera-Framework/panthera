<?php
/**
  * Panthera localisation class
  * Provides simple strings translation from serialized array files
  *
  * @package Panthera\core\locale
  *
  * @author Damian Kęska
  */

if (!defined('IN_PANTHERA'))
    exit;

class pantheraLocale
{
    protected $panthera, $locale, $defaultLocale = 'english', $defaultDomain = 'messages', $currentDomain = 'messages', $domains = array();

    // cache expiration time in seconds (set 0 to disable cache)
    protected $cache = 0;
    protected $invalidDomains = array(); // list of domains failed to load

    /**
     * Constructor, creates default values if any doesnt exists yet
     *
     * @return void
     * @author Damian Kęska
     */

    public function __construct($panthera)
    {
        $this->panthera = $panthera;
        $panthera->config->getKey('languages', array('polski' => True, 'english' => True), 'array');
        $this->defaultLocale = $panthera->config->getKey('language_default', 'english', 'string');

        // cache support
        if ($this->panthera->cache)
            $this->cache = $panthera->getCacheTime('locales');
    }

    /**
     * Get all locales
     *
     * @return array
     * @author Damian Kęska
     */

    public function getLocales() { return $this->panthera->config->getKey('languages'); }

    /**
     * Get all loaded domains
     *
     * @return array
     * @author Damian Kęska
     */

    public function getLoadedDomains() { return $this->domains; }

    /**
     * Get active locale (locale used in this session to translate strings)
     *
     * @return string
     * @author Damian Kęska
     */

    public function getActive() { return $this->locale; }

    /**
     * Get system default locale
     *
     * @return string
     * @author Damian Kęska
     */

    public function getSystemDefault() { return $this->defaultLocale; }

    /**
      * Check if locale exists
      *
      * @param string $localeName
      * @return bool
      * @author Damian Kęska
      */

    public function exists($localeName) { return array_key_exists($localeName, $this->getLocales()); }

    /**
     * Set system default locale
     *
     * @return bool
     * @author Damian Kęska
     */

    public function setSystemDefault($locale)
    {
        $locales = $this->panthera->config->getKey('languages');

        if(array_key_exists($locale, $locales))
        {
            $this->panthera->config->setKey('language_default', $locale, 'string');
            $this->defaultLocale = $locale;
            $this->locale = $locale;
            return True;
        }

        return False;
    }

    /**
     * Add new locale
     *
     * @return bool
     * @author Damian Kęska
     */

    public function addLocale($locale)
    {
        if(is_dir(SITE_DIR. '/content/locales/' .$locale. '/') or $locale == 'english' and $locale) // english should be hardcoded
        {
            $locales = $this->panthera->config->getKey('languages');
            $locales[$locale] = False;
            $this->panthera->config->setKey('languages', $locales);
            return True;
        }

        return False;
    }

    /**
     * Remove locale
     *
     * @return bool
     * @author Damian Kęska
     */

    public function removeLocale($locale)
    {
        $locales = $this->panthera->config->getKey('languages');

        if(array_key_exists($locale, $locales) and $locale != 'english') // english will be hardcoded, we must have any default
        {
            unset($locales[$locale]);
            $this->panthera->config->setKey('languages', $locales);
            return True;
        }

        return False;
    }

    /**
     * Activate or deactivate locale (the user can use it or not)
     *
     * @return bool
     * @author Damian Kęska
     */

    public function toggleLocale($locale, $value)
    {
        $locales = $this->panthera->config->getKey('languages');
        
        if (isset($locales[$locale]) or $locale == 'english')
        {
            $locales[$locale] = (bool)$value;
            $this->panthera->config->setKey('languages', $locales);
            return True;
        }
    }

    /**
     * Set locale as active
     *
     * @return string (active locale name)
     * @author Damian Kęska
     */

    public function setLocale($locale)
    {
        if(array_key_exists($locale, $this->panthera->config->getKey('languages')) or $locale == 'english')
            $this->locale = $locale;
        else
            $this->locale = $this->defaultLocale;

        $this->panthera->logging->output('setLocale(' .$locale. ')', 'pantheraLocale');

        // default domain should be always loaded
        $this->loadDomain('messages');

        return $this->locale;
    }

    /**
     * Translate string using active locale and domain
     *
     * @return string
     * @author Damian Kęska
     */

    public function _($string, $domain='')
    {
        if ($domain == '')
            $domain = $this -> currentDomain; // set current domain

        $orig = $string;

        if (!isset($this->memory[$domain]))
        {
            $this -> panthera -> logging -> output ('Autoloading domain "' .$domain. '" on-demand', 'pantheraLocale');
        
            if (isset($this->invalidDomains[$domain]))
            {
                return $string;
            }
            
            $this->loadDomain($domain);
        }

        // check if text exists in domain
        if (isset($this->memory[$domain][$string]))
            $string = $this->memory[$domain][$string];

        if ($this->panthera->logging->debug)
            $this->panthera->logging->output('localize -> "' .$orig. '", result="' .$string. '" domain='.$domain. ' (global: ' .$this->currentDomain. ')', 'pantheraLocale');

        return $string;
    }

    /**
     * Translate string using active locale and domain with specified variables inside of string
     *
     * @return string
     * @author Damian Kęska
     */

    public function f_($string, $domain, $variables)
    {
        return vsprintf($this->_($string, $domain), $variables);
    }

    /**
     * Set text domain as active
     *
     * @return bool
     * @author Damian Kęska
     */

    public function setDomain($domain)
    {
        if (is_file(SITE_DIR. '/content/locales/' .$this->locale. '/' .$domain. '.phps'))
        {
            $this->currentDomain = $domain;
            return True;
        }
    }

    /**
      * Load specified domain
      *
      * @param string $domain Domain name
      * @return bool
      * @author Damian Kęska
      */

    public function loadDomain($domain, $force=False)
    {
        // dont load same domains multiple times
        
        if (isset($this->domains[$domain]) and !$force)
            return False;
            
        $dirs = array(SITE_DIR. '/content/locales/' .$this->locale, PANTHERA_DIR. '/locales/' .$this->locale);

        foreach ($dirs as $dir)
        {
            if (is_file($dir. '/' .$domain. '.phps'))
            {
                if ($dir == PANTHERA_DIR. '/locales/' .$this->locale)
                    $this->domains[$domain] = 'lib';
                else
                    $this->domains[$domain] = 'content';
                    
                $this->panthera->logging->output('Adding domain "' .$domain. '" from ' .$dir, 'pantheraLocale');

                // read file from cache (to avoid IO read)
                if ($this->cache)
                {
                    if ($this->panthera->cache->exists('locale.'.$this->locale.'.'.$domain))
                    {
                        $this->memory[$domain] = $this->panthera->cache->get('locale.'.$this->locale.'.'.$domain);
                        $this->panthera->logging->output('Read id=locale.' .$this->locale. '.' .$domain. ' from cache', 'pantheraLocale');
                        return True;
                    }
                }

                $this->memory[$domain] = unserialize(file_get_contents($dir. '/' .$domain. '.phps'));

                if ($this->cache)
                {
                    $this->panthera->cache->set('locale.'.$this->locale.'.'.$domain, $this->memory[$domain], 'locales');

                    if ($this->panthera->logging->debug == True)
                        $this->panthera->logging->output('Wrote id=locale.' .$this->locale. '.' .$domain. ' to cache', 'pantheraLocale');

                }

                return True;
            }
        }
        
        $this->invalidDomains[$domain] = True;
        $this->panthera->logging->output('Cannot find domain "' .$domain. '"', 'pantheraLocale');
        return False;
    }

    /**
      * Try to detect browser's language
      *
      * @return void
      * @author Damian Kęska
      */

    public function fromHeader()
    {
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $languageTable = array('en' => 'english', 'en-us' => 'english', 'pl' => 'polski', 'de' => 'deutsh');
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ','));

            // applications may add their supported languages to this table
            $languageTable = $this->panthera->get_options('locale.header.langtable', $languageTable);

            if (isset($languageTable[$lang]))
                $this->setLocale($languageTable[$lang]);
        }
    }


    /**
     * Get user locale settings from current session
     *
     * @return bool
     * @author Damian Kęska
     */

    public function fromSession()
    {
        if (defined('SKIP_SESSION'))
            return False;

        $sessionKey = $this->panthera->config->getKey('session_key');

        if (isset($_GET['_locale']))
        {
            $locale = strtolower($_GET['_locale']); // selected by user
            $locales = $this->getLocales(); // all avaliable locales

            if (isset($locales[$locale]))
            {
                $locale = $this->panthera->get_filters('session_locale', $locale);

                if ($locales[$locale] == True) // if locale is enabled
                    $this->panthera->session->set('language', $locale);
            }
        }

        // set default locale if not choosed any
        if (!$this->panthera->session->exists('language'))
            $this->panthera->session->set('language', $this->defaultLocale);

        // set choosed or default locale
        $this->setLocale($this->panthera->session->get('language'));
        return True;
    }

    /**
      * Get locale name with override function
      *
      * @param string $override Locale name
      * @return string
      * @author Damian Kęska
      */

    public static function getFromOverride($override)
    {
        $panthera = pantheraCore::getInstance();
        $language = $panthera -> locale -> getActive();

        if ($panthera->locale->exists($override))
            $language = $override;

        return $language;
    }
    
    /**
      * Select a translated string from array with a translation priority
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function selectStringFromArray($input)
    {
        $panthera = pantheraCore::getInstance();
        
        if (!is_array($input))
        {
            return $input;
        }
        
        $language = $panthera -> locale -> getActive();
        
        if (isset($input[$language]))
        {
            return $input[$language];
        }
        
        if ($panthera->user)
        {
            if (isset($input[$panthera->user->language]))
            {
                return $input[$panthera->user->lantguage];
            }
        }
        
        if (isset($input['english']))
        {
            return $input['english'];
        }
        
        return end($input);
    }
    
    /**
      * Localize by array, string or serialized array example input: array('this is a string', 'domain_name') or serialize(array('this is a string', 'domain_name')) or jst "this is a string"
      *
      * @param string|array $input Serialized array, normal array or string
      * @param bool $getOriginalString Don't localize extracted string, just return it
      * @return string 
      * @author Damian Kęska
      */
    
    public function localizeFromArray($input, $getOriginalString=False)
    {
        if (is_string($input))
        {
            if (substr($input, 0, 2) == 'a:')
            {
                $tmp = unserialize($input);
                
                if ($getOriginalString)
                    return $tmp[0];
                
                return $this -> _($tmp[0], $tmp[1]);
            }
            
            if ($getOriginalString)
                return $input;
        
            return $this -> _($input); // localize using default domain
        }
           
        if (is_array($input))
        {
            if ($getOriginalString)
                return $input[0];
            
            if (count($input) == 1)
                $input[1] = '';
        
            return $this -> _($input[0], $input[1]);
        }
    }
}

/**
 * Translate string (useful in template system)
 *
 * @return string
 * @author Damian Kęska
 */

function localize($string, $domain='')
{
    $panthera = pantheraCore::getInstance();
    return $panthera->locale->_($string, $domain);
}

/**
 * Translate string (useful in template system) with variables inside
 *
 * @param string $string to be translated
 * @param string $domain Language domain eg. messages
 * @return string
 * @author Damian Kęska
 */

function slocalize($string, $domain)
{
    $panthera = pantheraCore::getInstance();

    $args = '';

    if (func_num_args() > 2)
    {
        $args = func_get_args();
        unset($args[0]); // string
        unset($args[1]); // domain
        //$args = array_reset_keys($args);
    }

    return $panthera->locale->f_($string, $domain, $args);
}
