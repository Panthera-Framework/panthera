<?php
/**
  * Displaying content 
  *
  * @package Panthera\core\templates
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
//include(PANTHERA_DIR. '/share/smarty/Smarty.class.php');
//include PANTHERA_DIR. '/share/dwoo/dwooAutoload.php'; 
//require PANTHERA_DIR. '/share/Twig/lib/Twig/Autoloader.php';
//Twig_Autoloader::register();

require PANTHERA_DIR. '/share/raintpl3/library/Rain/autoload.php';

/**
 * Panthera template wrapper
 *
 * @package Panthera\core\templates
 * @author Damian Kęska
 */

class pantheraTemplate extends pantheraClass
{
    protected $attributes = array('title' => '', 'keywords' => array(), 'metas' => array(), 'scripts' => array()), $panthera, $vars = array(), $cacheConfig = False;
    public $tpl, $name, $template, $generateScripts = True, $generateHeader = True, $generateMeta = True, $generateKeywords = True, $generateLinks = True, $header = '', $deviceType = 'desktop', $forceKeepTemplate = False, $timer = 0, $engine = 'raintpl3';
    
    // configurable options
    protected $debugging, $caching, $cache_lifetime;

    /**
	 * Set template as active (not a single template eg. index.tpl but a set of templates in directory eg. admin => /content/templates/admin)
	 *
     * @param string (template name)
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
                $this->panthera->cache->set('tpl.cfg.' .$template, $tpl, 3600); // 1 hour by default (for debugging please just disable caching option)
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
        
        if (array_key_exists('tablet_template', $tpl))
            $this->push('tabletTemplate', True);
            
        if (array_key_exists('mobile_template', $tpl))
            $this->push('mobileTemplate', True);
            
        if (array_key_exists('mobile', $tpl) or array_key_exists('tablet', $tpl))
            $this->push('usingMobileTemplate', $tpl);

        $this->template = $tpl;
        $this->name = $template;
        
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

    function __construct($panthera)
    {
        parent::__construct($panthera);
   
        // some configuration variables
        $this->debugging = (bool)$this->panthera->config->getKey('template_debugging', False, 'bool'); // TODO: A template that displays Panthera environment
        $this->caching = (bool)$this->panthera->config->getKey('template_caching', False, 'bool');
        $this->cache_lifetime = intval($this->panthera->config->getKey('template_cache_lifetime', 120, 'int'));
        
        //$cacheDir = False;
        //if ($this->caching == True)
        $cacheDir = SITE_DIR. '/content/tmp/templates_c/';
        
        // configure RainTPL engine
        Rain\Tpl::configure(array("base_url" => null, "tpl_dir"	=> '/', "cache_dir"	=> $cacheDir, "tpl_ext" => 'tpl', "debug" => $this->debugging, 'auto_escape' => false, 'php_enabled' => true, 'sandbox' => false));
        $this->tpl = new Rain\Tpl;
        #\Rain\Tpl::registerTag('stringModifier', '{"([^}"]+)"|([a-zA-Z\"]+):?([A-Za-z0-9\"]+)?:?([A-Za-z0-9\"]+)?:?([A-Za-z0-9\"]+)?}', function( $params, $b ){ var_dump($params); } );
                
        
        // Force keep default template
        if ($panthera->session->get('tpl_forceKeepTemplate'))
        {
            $this->forceKeepTemplate = True;
        }

        if ($this->panthera->cacheType('cache') == 'memory' and $this->caching == True)
        {
            if ($this->cache_lifetime > 0)
            {
                // dwoo
                //$this->tpl->setCacheDir(SITE_DIR.'/content/tmp/cache/');
                //$this->tpl->setCacheTime($this->cache_lifetime);
            }
            
            // cache configuration files?
            $this->cacheConfig = True;
        }
        
        // automatic generate site header from config informations
        if (!defined('TPL_NO_AUTO_HEADER'))
        {
            $this -> setTitle($this->panthera->config->getKey('site_title'));
            $this -> addMetaTag('description', $this->panthera->config->getKey('site_description'));
            $this -> putKeywords(explode(',', $this->panthera->config->getKey('site_meta')));
        }
        //$this->tpl->plugins_dir = array(PANTHERA_DIR.'/smarty/sysplugins/', PANTHERA_DIR.'/share/smarty/plugins/', SITE_DIR.'/content/smartyplugins/', PANTHERA_DIR.'/share/smartyplugins/');
        
        // automatic webroot merge (for debugging purposes)
        if ($this->debugging)
            $this->webrootMerge();
    }

    /**
	 * Push a PHP variable to template ( works like smarty->assign() )
	 *
     * @param string $key
     * @param mixed $value
	 * @return bool
	 * @author Damian Kęska
	 */

    function push ($key, $value)
    {
        //$this->tpl->assign($key, $value, 'nocache');
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

    function setTitle($title)
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

    function putKeywords($keywords)
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

    function getKeywords()
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

    function removeKeyword($keyword)
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

    function addMetaTag($meta, $content)
    {
        $this->attributes['metas'][$meta] = $content;
        return True;
    }

    /**
	 * Get all meta tags
	 *
	 * @return array
	 * @author Damian Kęska
	 */

    function getMetaTags()
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

    function removeMetaTag($tagName)
    {
        unset($this->attributes['metas'][$tagName]);
        return True;
    }

    /**
	 * Add Google site verification identity
	 *
     * @param string $key A key you received from Google to verify your website
	 * @return bool
	 * @author Damian Kęska
	 */

    function googleSiteVerification($key)
    {
        $this->addMetaTag('google-site-verification', $key);
        return True;
    }

    /**
	 * Include javascript file
	 *
     * @param string $src
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addScript($src)
    {
        $this->attributes['scripts'][md5($src)] = array('src' => $src, 'content' => '');
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
	 * Add javascript content
	 *
     * @param string $content
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addScriptContent($content)
    {
        $this->attributes['scripts'][md5($content)] = array('src' => '', 'content' => $content);
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
	 * Add Open Search
	 *
     * @param string $src
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addOpenSearch($href, $title='')
    {
        $this->addLink($href, 'application/opensearchdescription+xml', 'search', $title);
        return True;
    }

    /**
	 * Remove link
	 *
     * @param string $href 
	 * @return bool
	 * @author Damian Kęska
	 */

    function removeLink($href)
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
	 * @return bool
	 * @author Damian Kęska
	 */

    function redirect($location, $type)
    {
        switch ($type)
        {
            case 'header':
                header('Location: ' .$location);
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

            // by default return the location
            default:
                return $location;
            break;
        }
    }

    /**
	 * Display template
	 *
     * @param string (variable name), mixed (value)
	 * @return bool
	 * @author Damian Kęska
	 */

    function display($template=NuLL, $renderOnly=False)
    {
        $this->timer = microtime_float();
        
        #foreach ($this->vars as $key => $value)
            #$this->tpl->assign($key, $value);

        if ($this->generateHeader == True)
        {
            $header = $this->header;

            if ($this->generateLinks == True)
            {
                // css styles, open search etc.
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
                    $header .= '<meta name="' .filterMetaTag($key). '" content="' .filterMetaTag($value). '">';
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
            $this->push('site_title', $this->attributes['title']);
            $this->push('site_header', $header);
        }


        if ($template == NuLL)
            $template = $this->template['index'];
            
        // execute hooks
        $this->panthera->get_options('template.display', $template);
            
        $file = getContentDir('/templates/' .$this->name. '/templates/' .$template);

        if (!$file)
            throw new Exception('Cannot find template "' .$template. '" in both /content/templates/' .$this->name. '/templates and /lib/templates/' .$this->name. '/templates directories');
            
        $this->panthera->logging->output('Displaying ' .$file, 'pantheraTemplate');

        // assign all variables        
        foreach ($this->vars as $var => $value)
            $this -> tpl -> assign($var, $value);
            
        $render = $this -> tpl -> draw(str_replace('.tpl', '', $file), True, True);
            
        if ($renderOnly == True)
            return $render;        
        else
            print($render);
            
        @ob_end_flush();
        
        // generate template execution time
        $this -> timer = (microtime_float() - $this -> timer);
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
        deleteDirectory(SITE_DIR. '/content/tmp/templates_c');
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
    
    public function compile($template)
    {
        return $this->display($template, True);
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
            $path = getContentDir('templates/' .$template);
            
        if ($path)
            return json_decode(file_get_contents($path. '/config.json'), true);

        return null;
    }
    
    /**
      * List all templates in /lib and /content
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function listTemplates($template=False)
    {
        $templates = array();
    
        if ($template == False)
        {
            $pantheraTemplates = scandir(PANTHERA_DIR.'/templates');
            $contentTemplates = scandir(SITE_DIR. '/content/templates');
            
            if (count($pantheraTemplates) > 0)
            {
                foreach ($pantheraTemplates as $file)
                {
                    if ($file == '..' or $file == '.' or !is_dir(PANTHERA_DIR.'/templates/' .$file))
                        continue;
                        
                    $templates[$file] = array('item' => PANTHERA_DIR.'/templates/' .$file, 'place' => 'lib');
                }
            }
            
            if (count($contentTemplates) > 0)
            {
                foreach ($contentTemplates as $file)
                {
                    if ($file == '..' or $file == '.' or !is_dir(SITE_DIR.'/content/templates/' .$file))
                        continue;
                        
                    $templates[$file] = array('item' => SITE_DIR.'/content/templates/' .$file, 'place' => 'content');
                }
            }
            
        } else {
            // list files of given template
            $pantheraFiles = scandir(PANTHERA_DIR.'/templates/' .$template. '/templates');
            $contentFiles = scandir(SITE_DIR.'/content/templates/' .$template. '/templates');
            
            if (count($pantheraFiles) > 0)
            {
                foreach ($pantheraFiles as $file)
                {
                    if ($file == '..' or $file == '.' or !is_file(PANTHERA_DIR.'/templates/' .$template. '/templates/' .$file))
                        continue;
                
                    $templates[$file] = array('item' => PANTHERA_DIR.'/templates/' .$template. '/templates/' .$file, 'place' => 'lib');
                }
            }
            
            if (count($contentTemplates) > 0)
            {
                foreach ($contentTemplates as $file)
                {
                    if ($file == '..' or $file == '.' or !is_file(SITE_DIR.'/content/templates/' .$template. '/templates/' .$file))
                        continue;
                        
                    $templates[$file] = array('item' => SITE_DIR.'/content/templates/' .$template. '/templates/' .$file, 'place' => 'lib');
                }
            }
        
        }
        
        return $templates;
    }
    
    /** CSS, JS, Images and other files cache **/
    
    /**
      * Find all files to update from templates and copy them
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function webrootMerge($customTemplates=array())
    {
        //$lastMod = $this -> panthera -> varCache -> get('webroot_checktime'); // last file modification
        $mainTemplate = $this -> panthera -> config -> getKey('template');
        
        $roots = array (
                      PANTHERA_DIR.'/templates/admin/webroot', 
                      SITE_DIR. '/content/templates/admin/webroot', 
                      PANTHERA_DIR.'/templates/admin_mobile/webroot', 
                      SITE_DIR. '/content/templates/admin_mobile/webroot',
                      PANTHERA_DIR.'/templates/' .$mainTemplate. '/webroot', 
                      SITE_DIR. '/content/templates/' .$mainTemplate. '/webroot'
                    );
                    
        if (!empty($customTemplates))
        {
            foreach ($customTemplates as $template)
            {
                $roots[] = PANTHERA_DIR. '/templates/' .$template. '/webroot';
                $roots[] = SITE_DIR. '/content/templates/' .$template. '/webroot';
            }
        }
                            
        $this->panthera->importModule('filesystem');
        
        // array with list of changes
        $changes = array();
        
        $this -> panthera -> logging -> output ('Starting webrootMerge', 'pantheraTemplate');

        foreach ($roots as $dir)
        {
            if (is_dir($dir))
            {
                $files = scandirDeeply($dir, False);
                
                // directories first need to be created
                foreach ($files as $file)
                {
                    if (is_dir($file))
                    {
                        // get directory address inside of root $dir
                        $chroot = str_replace($dir, '', $file);
                        
                        if ($chroot == '')
                            continue;
                            
                        if (!is_dir(SITE_DIR. '/' .$chroot))
                        {
                            $this->panthera->logging->output('Creating a directory ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate');
                            mkdir(SITE_DIR. '/' .$chroot);
                            $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'dir', 'chrootname' => $chroot, 'source' => $file);
                        } else
                            $changes[] = array('status' => False, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'dir', 'chrootname' => $chroot, 'source' => $file);
                    }
                }
                
                // now just simply copy files
                foreach ($files as $file)
                {
                    if(is_file($file))
                    {
                        // get file address inside of root $dir
                        $chroot = str_replace($dir, '', $file);
                        
                        // copy file if it does not exists
                        if (!is_file(SITE_DIR. '/' .$chroot))
                        {
                            $this->panthera->logging->output('Creating file ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate');
                            copy($file, SITE_DIR. '/'.$chroot);
                            $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                        } else {
                        
                            // compare file dates
                            if (filemtime($file) > filemtime(SITE_DIR. '/' .$chroot))
                            {
                                $this->panthera->logging->output('Copying outdated file ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate');
                                copy($file, SITE_DIR. '/'.$chroot);
                                $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                            } else
                                $changes[] = array('status' => False, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                        }
                    }
                }
            }
        }   
        
        return $changes;
        
    }
}
