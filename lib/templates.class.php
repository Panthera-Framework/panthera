<?php
/**
  * Displaying content 
  *
  * @package Panthera\core\templates
  * @author Damian Kęska
  * @license GNU Lesser General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
require PANTHERA_DIR. '/share/raintpl3/library/Rain/autoload.php';

/**
 * Panthera template wrapper
 *
 * @package Panthera\core\templates
 * @author Damian Kęska
 */

class pantheraTemplate extends pantheraClass
{
    protected $attributes = array(
        'title' => '',
        'keywords' => array(),
        'metas' => array(),
        'scripts' => array(),
        'links' => array()
    );
    protected $panthera;
    public $vars = array();
    protected $cacheConfig = False;
    public $tpl;
    public $name;
    public $template;
    public $generateScripts = True;
    public $generateHeader = True;
    public $generateMeta = True;
    public $generateKeywords = True;
    public $generateLinks = True;
    public $header = '';
    public $deviceType = 'desktop';
    public $forceKeepTemplate = False;
    public $timer = 0;
    public $engine = 'raintpl';
    public $icons = null; // array
    
    // configurable options
    protected $debugging;
    protected $caching;
    protected $cache_lifetime;

    /**
	 * Set template as active (not a single template eg. index.tpl but a set of templates in directory eg. admin => /content/templates/admin)
	 *
     * @param string $template Template directory name eg. "admin"
	 * @return bool
	 * @author Damian Kęska
	 */

    public function setTemplate($template)
    {
        $tpl = null;
        
        // template redirections
        if ($this->panthera->session->exists('template.force'))
        {
            $force = $this -> panthera -> session -> get('template.force');
            
            if ($template == $force[0])
                $template = $force[1];
        }
        
        if ($this->cacheConfig == True)
        {
            if ($this->panthera->cache->exists('tpl.cfg.' .$template))
            {
                $tpl = $this->panthera->cache->get('tpl.cfg.' .$template);
                $this->panthera->logging->output('Read id=tpl.cfg.' .$template. ' from cache', 'pantheraTemplate');
            }
        }

        if ($tpl == null)
        {
            $tpl = $this -> getTemplateConfig($template);
            
            if ($tpl == NuLL)
                throw new Exception('Invalid template: $tpl variable was not set in '.SITE_DIR.'/content/templates/' .$template. '/config.json');
                
            if ($this->cacheConfig == True)
            {
                $this->panthera->cache->set('tpl.cfg.' .$template, $tpl, 'templates'); // 1 hour by default (for debugging please just disable caching option)
                $this->panthera->logging->output('Wrote id=tpl.cfg.' .$template. ' to cache', 'pantheraTemplate');
            }
        }
        
        // mobile browser detection
        if (!defined('DISABLE_BROWSER_DETECTION') and !isset($force) and !$this->panthera->session->exists('template.force.skip'))
        {
            $browser = $this -> panthera -> session -> get('clientInfo');
                
            if ($browser -> deviceType == 'mobile' and array_key_exists('mobile_template', $tpl))
            {
                if ($tpl['mobile_template'] != $template)
                {
                    $this->panthera->session->set('template.force', array($template, $tpl['mobile_template'])); // set template redirection eg. from admin to admin_mobile
                    return $this->setTemplate($tpl['mobile_template']);
                }
                        
            } elseif ($browser -> deviceType == 'tablet' and array_key_exists('tablet_template', $tpl)) {
                
                if ($tpl['tablet_template'] != $template)
                {
                    $this->panthera->session->set('template.force', array($template, $tpl['tablet_template']));
                    return $this->setTemplate($tpl['mobile_template']);
                }
            }
        }
        
        if (isset($tpl['tablet_template']))
            $this->push('tabletTemplate', True);
            
        if (isset($tpl['mobile_template']))
            $this->push('mobileTemplate', True);
            
        if (isset($tpl['mobile']) or isset($tpl['tablet']))
            $this->push('usingMobileTemplate', $tpl);

        // add template settings from config.json as $_tpl_settings variable inside of every template
        $this -> push ('_tpl_settings', $tpl);
        $this -> template = $tpl;
        $this -> name = $template;
        
        //Rain\Tpl::configure('allow_compile', False);
        
        // reconfiguring RainTPL
        Rain\Tpl::configure('include_path', array(
            SITE_DIR. '/content/templates/' .$template. '/templates/',
            PANTHERA_DIR. '/templates/' .$template. '/templates/',
        ));
        
        // switching device type
        if (isset($_GET['__switchdevice']) and !defined('DISABLE_DEVICES_SWITCH'))
            $this -> panthera -> importModule('boot/switchdevice');
    }
    
    /**
	 * Initialize template system, configuration etc.
	 *
     * @param string (variable name), mixed (value)
	 * @return bool
	 * @author Damian Kęska
	 */

    public function __construct($panthera)
    {
        parent::__construct($panthera);
   
        // some configuration variables
        $this->debugging = (bool)$this->panthera->config->getKey('template_debugging', False, 'bool'); // TODO: A template that displays Panthera environment
        $this->caching = (bool)$this->panthera->config->getKey('template_caching', False, 'bool');
        $this->cache_lifetime = intval($this->panthera->config->getKey('template_cache_lifetime', 120, 'int'));
        
        //$cacheDir = False;
        //if ($this->caching == True)
        $cacheDir = SITE_DIR. '/content/tmp/templates_c/';
        
        // TODO: Add support for include_dir
        
        // configure RainTPL engine
        Rain\Tpl::configure(array(
            "base_url" => null, 
            "tpl_dir"	=> '/',
            'include_path' => array(), 
            "cache_dir"	=> $cacheDir, 
            "tpl_ext" => 'tpl', 
            "debug" => $this->debugging, 
            'auto_escape' => false, 
            'php_enabled' => true, 
            'sandbox' => false
        ));
        
        $this->tpl = new Rain\Tpl;
        #\Rain\Tpl::registerTag('stringModifier', '{"([^}"]+)"|([a-zA-Z\"]+):?([A-Za-z0-9\"]+)?:?([A-Za-z0-9\"]+)?:?([A-Za-z0-9\"]+)?}', function( $params, $b ){ var_dump($params); } );
        
        // Force keep default template
        if ($panthera->session->get('tpl_forceKeepTemplate'))
        {
            $this->forceKeepTemplate = True;
        }

        if ($this->panthera->cacheType('cache') == 'memory' and $this->caching == True)
        {
            /*if ($this->cache_lifetime > 0)
            {
                // dwoo
                //$this->tpl->setCacheDir(SITE_DIR.'/content/tmp/cache/');
                //$this->tpl->setCacheTime($this->cache_lifetime);
            }*/
            
            // cache configuration files?
            $this->cacheConfig = True;
        }
        //$this->tpl->plugins_dir = array(PANTHERA_DIR.'/smarty/sysplugins/', PANTHERA_DIR.'/share/smarty/plugins/', SITE_DIR.'/content/smartyplugins/', PANTHERA_DIR.'/share/smartyplugins/');
        
        // automatic webroot merge (for debugging purposes)
        if ($this->debugging)
        {
            $this -> panthera -> importModule('libtemplate');
            libtemplate::webrootMerge();
        }
    }

    /**
	 * Push a PHP variable to template (works like smarty->assign())
	 *
     * @param string|array $key Key name, or array with multiple keys
     * @param mixed $value Value for single key, optional if first argument is an array
	 * @return bool
	 * @author Damian Kęska
	 */

    public function push ($key, $value='')
    {
        if (is_array($key))
        {
            $this -> vars = array_merge($this -> vars, $key);
            return True;
        }

        $this->vars[$key] = $value;
        return True;
    }

    /**
	 * Set page title
	 *
     * @param string $title
	 * @return bool
	 * @author Damian Kęska
	 */

    public function setTitle($title)
    {
        $this->attributes['title'] = $title;
        return True;
    }

    /**
	 * Add keywords to meta tags
	 *
     * @param array $keywords Format: array('one', 'two', 'three', 'four', ...)
	 * @return bool
	 * @author Damian Kęska
	 */

    public function putKeywords($keywords)
    {
        if (!is_array($keywords))
            return False;

        $this->attributes['keywords'] = array_merge($this->attributes['keywords'], $keywords);
        return True;
    }

    /**
	 * Return exising keywords
	 *
	 * @return array
	 * @author Damian Kęska
	 */

    public function getKeywords()
    {
        return $this->attributes['keywords'];
    }

    /**
	 * Remove keyword
	 *
     * @param string $keyword 
	 * @return bool
	 * @author Damian Kęska
	 */

    public function removeKeyword($keyword)
    {
        if(($key = array_search($keyword, $this->attributes['keywords'])) !== false) {
            unset($this->attributes['keywords'][$key]);
            return True;
        }

        return False;
    }

    /**
	 * Add meta tag
	 *
     * @param string $meta Meta tag name eg. google-site-verification
     * @param string $content Tag content eg. z5mJLjVGtEe5qzCefW1pamxI7H46u19n4XnxEzgl1AU
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addMetaTag($meta, $content, $isProperty=False)
    {
        if ($isProperty)
        {
            $meta .= '::property';
        }
        
        $this->attributes['metas'][$meta] = $content;
        return True;
    }

    /**
	 * Get all meta tags
	 *
	 * @return array
	 * @author Damian Kęska
	 */

    public function getMetaTags()
    {
        return $this->attributes['metas'];
    }

    /**
	 * Remove meta tag
	 *
     * @param string $tagName
	 * @return bool
	 * @author Damian Kęska
	 */

    public function removeMetaTag($tagName)
    {
        unset($this->attributes['metas'][$tagName]);
        return True;
    }

    /**
	 * Include javascript file
	 *
     * @param string $src
     * @param string $content
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addScript($src, $content='')
    {
        if ($content)
        {
            $id = hash('md4', $content);
        } else {
            $id = hash('md4', $src);
        }
    
        $this->attributes['scripts'][$id] = array('src' => $src, 'content' => $content);
        return True;
    }

    /**
	 * Remove javascript file
	 *
     * @param string $src
	 * @return bool
	 * @author Damian Kęska
	 */

    public function removeScript($src)
    {
        unset($this->attributes['scripts'][md5($src)]);
        return True;        
    }

    /**
	 * Link file, eg. css
	 *
     * @param string $src
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addLink($href, $type='', $rel='', $title='')
    {
        $this->attributes['links'][] = array('href' => $href, 'title' => $title, 'type' => $type, 'rel' => $rel);
        return True;
    }

    /**
	 * Link CSS file
	 *
     * @param string $src
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addStyle($href, $title='')
    {
        $this->addLink($href, 'text/css', 'stylesheet', $title='');
        return True;
    }

    /**
	 * Remove link
	 *
     * @param string $href 
	 * @return bool
	 * @author Damian Kęska
	 */

    public function removeLink($href)
    {
        if(($key = array_search($href, $this->attributes['links'])) !== false) {
            unset($this->attributes['links'][$key]);
            return True;
        }

        return False;
    }

    /**
	 * Redirect browser to other location eg. facebook authorization url
     * Types of redirection:
     *  - meta: as meta tag
     *  - script: setting window.location.href via javascript (works on most browsers, bypasses ajax)
     *  - header: using HTTP "Location" header
	 *
     * @param string $location URL where to redirect browser
     * @param string $type Type of redirection, avaliable types: header, meta, script
     * @param int $code Optional header response code, default is 302
	 * @return bool
	 * @author Damian Kęska
	 */

    public function redirect($location, $type, $code=302)
    {
        switch ($type)
        {
            case 'header':
                header('Location: ' .$location, True, $code);
                pa_exit();
            break;

            case 'meta':
                print('<html><head><title>Redirection</title><meta http-equiv="refresh" content="0;URL=\'' .$location. '\'"></head><body>Click <a href="' .$location. '">here</a> if your browser didnt redirect you automaticaly</body></html>');
                pa_exit();
            break;

            case 'script':
                print('<html><head><title>Redirection</title><script type="text/javascript">window.location = "' .$location. '";</script></head><body>Click <a href="' .$location. '">here</a> if your browser didn\'t redirect you automaticaly.</body></html>');
                pa_exit();
            break;
            
            case 'script_frame':
                print('<html><head><title>Redirection</title><script type="text/javascript">window.top.location = "' .$location. '";</script></head><body>Click <a href="' .$location. '">here</a> if your browser didn\'t redirect you automaticaly.</body></html>');
                pa_exit();
            break;

            // by default return the location
            default:
                return $location;
            break;
        }
    }
    
    /**
      * Get template file configuration stored in "configs" directory
      *
      * @param string $template name
      * @return array|bool 
      * @author Damian Kęska
      */
    
    public function getFileConfig($template)
    {
        $this->panthera->logging->output('Looking for template file config for ' .$this->name. '/templates/' .$template, 'pantheraTemplate');
    
        if ($this->panthera->cache)
        {
            if ($this->panthera->cache->exists('tpl.filecfg.' .$template))
            {
                return (object)$this -> panthera -> cache -> get ('tpl.filecfg.' .$template);
            }
        }
        
        $configDir = getContentDir('/templates/' .$this->name. '/configs/' .$template. '.json');
        
        if ($configDir)
        {
            $contents = json_decode(file_get_contents($configDir), True);
            
            if (!$contents)
            {
                $this -> panthera -> logging -> output ('Invalid JSON syntax or empty file in ' .$configDir, 'pantheraTemplate');
                return False;
            }
        
            // update cache
            if ($this->panthera->cache)
            {
                $this -> panthera -> cache -> set('tpl.filecfg.' .$template, $contents, 'templates');
            }
            
            return (object)$contents;
        }
        
        // if nothing loaded
        return False;
    }

    /**
     * Get sock icon path/class name (depends on used template)
     * 
     * @param string $iconName Icon entry name
     * @return string Icon class name or path
     */

    public function getStockIcon($iconName)
    {
        if ($this->icons === null)
        {
            $this -> loadStockIcons();
        }
        
        if (!isset($this->icons[$iconName]))
            return '';
        
        return pantheraUrl($this->icons[$iconName], false, 'frontend');
    }
    
    /**
     * Load stock items
     * 
     * @return bool|null
     */
    
    public function loadStockIcons()
    {
        $iconSet = null;
        //$this -> panthera -> cache -> remove('tpl.icons.' .$this->name);
        
        if ($this -> panthera -> cache)
        {
            if ($this -> panthera -> cache -> exists('tpl.icons.' .$this->name))
            {
                $iconSet = $this -> panthera -> cache -> get('tpl.icons.' .$this->name);
                $this -> icons = $iconSet;
                $this -> panthera -> logging -> output('Loaded list of ' .count($iconSet). ' icons from cache (tpl.icons.' .$this->name. ')', 'pantheraTemplate');
            }
        }
        
        if ($iconSet === null)
        {
            if ($f = getContentDir('templates/' .$this->name. '/icons.ini'))
            {
                $iconSet = parse_ini_file($f);
                $this->icons = $iconSet;
                
                if ($this -> panthera -> cache)
                {
                    $this -> panthera -> cache -> set('tpl.icons.' .$this->name, $iconSet, 3600);
                    $this -> panthera -> logging -> output('Wrote list of ' .count($iconSet). ' icons to cache (tpl.icons.' .$this->name. ')', 'pantheraTemplate');
                }
                
                return true;
            }
        }
    }

    /**
	 * Display template
	 *
	 * @hook template.display $template
     * @param string (variable name), mixed (value)
     * @param bool $renderOnly Render template to string
     * @param bool $skipHooking
     * @param array $vars Variables to pass to template
	 * @return bool
	 * @author Damian Kęska
	 */

    public function display($template=NuLL, $renderOnly=False, $skipHooking=False, $vars='', $altTemplateDir='')
    {
        $this->timer = microtime_float();
        
        // execute hooks
        if (!$skipHooking)
        {
            $template = $this->panthera->get_filters('template.display', $template, True, 
            array(
                'skipHooking' => $skipHooking,
                'renderOnly' => $renderOnly,
                'additionalVars' => $vars,
                'altTemplateDir' => $altTemplateDir,
            ));
        }
        
        $siteTitle = pantheraLocale::selectStringFromArray($this->panthera->config->getKey('site_title'));
        
        if (!$siteTitle)
        {
            $siteTitle = 'Panthera';
        }
        
        $this->push('siteTitle', $siteTitle);
        
        #foreach ($this->vars as $key => $value)
            #$this->tpl->assign($key, $value);

        if ($this->generateHeader == True)
        {
            // automatic generate site header from config informations
            if (!defined('TPL_NO_AUTO_HEADER'))
            {
                if (!$this->attributes['title'])
                    $this -> setTitle($siteTitle);
                    
                $this -> addMetaTag('description', pantheraLocale::selectStringFromArray($this->panthera->config->getKey('site_description')));
                $this -> putKeywords(explode(',', pantheraLocale::selectStringFromArray($this->panthera->config->getKey('site_metas'))));
            }
        
            $header = $this->header;

            if ($this->generateLinks == True)
            {
                // css styles, open search etc.
                if ($this->attributes['links'])
                {
                    foreach ($this->attributes['links'] as $key => $value)
                    {
                        $link = '<link href="' .filterMetaTag($value['href']). '"';
    
                        if ($value['title'] != '')
                            $link .= ' title="' .filterMetaTag($value['title']). '"';
    
                        if ($value['rel'] != '')
                            $link .= ' rel="' .filterMetaTag($value['rel']). '"';
    
                        if ($value['type'] != '')
                            $link .= ' type="' .filterMetaTag($value['type']). '"';
    
                        $link .= '>';
    
                        $header .= $link."\n";
                    }
                }
            }


            if ($this->generateScripts == True)
            {
                // add all scripts
                foreach ($this->attributes['scripts'] as $key => $value)
                {
                    $script = '<script type="text/javascript"';

                    if ($value['src'] != '')
                        $script .= ' src="' .filterMetaTag($value['src']). '"';

                    $script .= '>' .$value['content']. '</script>';

                    $header .= $script."\n";
                }
            }

            if ($this->generateMeta == True)
            {
                // put all metas
                foreach ($this->attributes['metas'] as $key => $value)
                {
                    $exp = explode('::', $key);
                    
                    if (count($exp) == 1)
                        $exp[1] = 'name';
                    else
                        $exp[1] = 'property';
                    
                    $header .= '<meta ' .$exp[1]. '="' .filterMetaTag($exp[0]). '" content="' .filterMetaTag($value). '">';
                    $header .= "\n";
                }
            }

            if ($this->generateKeywords == True)
            {
                // put all keywords
                if (count($this->attributes['keywords']) > 0)
                {
                    $header .= '<meta name="keywords" content="' .parseMetaTags($this->attributes['keywords']). '">';
                    $header .= "\n";
                }
            }
            
            // parse Panthera internal urls
            $header = pantheraUrl($header);
            
            // allow plugis modify header variables
            $this->attributes['title'] = $this->panthera->get_filters('site_title', $this->attributes['title']);
            $header = $this->panthera->get_filters('site_header', $header);

            // push headers to template
            //$this->push('site_title', $this->attributes['title']);
            
            if ($this->attributes['title'])
                $header .= "\n<title>".$this->attributes['title']."</title>";
            
            $this->push('site_header', $header);
        }


        if (!$template)
        {
            $template = $this->template['index'];
        }
        
        if ($altTemplateDir)
        {
            $file = getContentDir('/templates/' .$altTemplateDir. '/templates/' .$template);
        } else {
            $file = getContentDir('/templates/' .$this->name. '/templates/' .$template);
        }
        
        if (!$file)
            throw new Exception('Cannot find template "' .$template. '" in both /content/templates/' .$this->name. '/templates and /lib/templates/' .$this->name. '/templates directories');
            
        $this->panthera->logging->output('Displaying ' .$file, 'pantheraTemplate');
        
        // turn off output control
        $this->panthera->outputControl->flushAndFinish();
        
        if (!$vars)
            $vars = $this->vars;
        
        // add active language to variables array
        $vars['language'] = $this->panthera->locale->getActive();
        
        // assign all variables from pantheraTemplate to template engine
        foreach ($vars as $var => $value)
            $this -> tpl -> assign($var, $value);
            
        $render = $this -> tpl -> draw(str_replace('.tpl', '', $file), True);
        $this -> timer = (microtime_float() - $this -> timer);
        
        if (!$skipHooking)
            $render = $this->panthera->get_filters('template.display.rendered', $render, True);
            
        if ($renderOnly == True)
            return $render;        
        else {
            // gzip compression
            if ($this->panthera->config->getKey('gzip_compression', False, 'bool'))
                $this->panthera->outputControl->startBuffering('ob_gzhandler');
                
            print($render);
            
            $this->panthera->outputControl->flushAndFinish();
        }
    }
    
    /**
      * Clear file cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clearCache()
    {
        $this->panthera->importModule('filesystem');
        filesystem::deleteDirectory(SITE_DIR. '/content/tmp/templates_c');
        mkdir(SITE_DIR. '/content/tmp/templates_c');
        return true;
    }
    
    /**
      * Compile template and return result (not display)
      *
      * @param string $template
      * @return string 
      * @author Damian Kęska
      */
    
    public function compile($template, $skipHooking=False, $vars='', $altTemplateDir='')
    {
        return $this->display($template, True, $skipHooking, $vars, $altTemplateDir);
    }
    
    /**
      * Returns template configuration as array
      *
      * @param string $template name or path
      * @return array|null 
      * @author Damian Kęska
      */
    
    public function getTemplateConfig($template)
    {
        if (strpos($template, '/'))
        {
            $path = $template;
        } else
            $path = getContentDir('templates/' .$template. '/config.json');
            
        if ($path)
            return json_decode(file_get_contents($path), true);

        return null;
    }
}

/**
 * Output control for Panthera Framework
 *
 * @package Panthera\core\templates
 * @author Damian Kęska
 */

class outputControl extends pantheraClass
{
    protected $log = '';
    protected $handler = False;
    
    /**
      * Save output buffer to variable that can be added to Panthera Logging
      *
      * @return bool Returns true if started buffering, and returns false if buffering is already running
      * @author Damian Kęska
      */

    public function startBuffering($handler='')
    {
        global $panthera;
        
        if ($this->isEnabled())
        {
            return False;
        }
    
        @ob_flush();
        
        if ($panthera)
            $panthera->logging->output('Setting output buffering with "' .$handler. '" handler', 'outputControl');

        if ($handler == 'log')
        {
            @ob_start('obLogHandler');
        } elseif (!$handler) {
            @ob_start();
        } else {
            @ob_start($handler);
        }
        
        $this -> handler = $handler;
        
        return True;
    }
    
    /**
      * Handle write from PHP's ob
      *
      * @param string $string
      * @param int $phase
      * @return bool 
      * @author Damian Kęska
      */
    
    public function handle($string, $phase)
    {
        $this->log .= $string;
        return True;
    }
    
    /**
      * Check if output buffering is enabled
      *
      * @return string 
      * @author Damian Kęska
      */
    
    public function isEnabled()
    {
        return $this->handler;
    }
    
    /**
      * Get saved output
      *
      * @return string 
      * @author Damian Kęska
      */
    
    public function get()
    {
        if (!$this->handler)
        {
            return @ob_get_contents();
        }
    
        return $this->log;
    }
    
    /**
      * Clean output buffering
      *
      * @author void
      * @author Damian Kęska
      */
    
    public function clean()
    {
        @ob_clean();
        $this -> log = '';
    }
    
    /**
     * Flush output
     *
     * @return null
     */
    
    public function flush()
    {
        ob_flush();
    }
    
    /**
      * Flush output to browser and finish output buffering
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function flushAndFinish()
    {
        global $panthera;
        
        @ob_end_flush();
        
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        
        $this -> handler = False;
        $panthera->logging->output('Flushing buffers and stopping output buffering', 'outputControl');
    }
}

/**
  * Used to handle output buffering in PHP
  *
  * @package Panthera\core\templates
  * @param string $string
  * @param string $phase
  * @return bool
  * @author Damian Kęska
  */

function obLogHandler($string, $phase)
{
    global $panthera;
    return $panthera->outputControl->handle($string, $phase);
}
