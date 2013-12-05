<?php
/**
  * Cloned - mass content ripping module
  * 
  * @package Panthera\modules\cloned
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
global $panthera;
require_once PANTHERA_DIR. '/share/phpQuery.php';
$panthera -> importModule('simpleimage');
$panthera -> importModule('httplib');

/**
  * Mass content ripping module class
  *
  * @package Panthera\modules\cloned
  * @author Damian Kęska
  */

class cloned extends pantheraClass
{
    // links to parse
    protected $links = array();

    /**
      * Add link to parsing queue
      * Returns false when input $link is not a valid url address, or when $parser was not found
      *
      * @param string $link
      * @return bool
      * @author Damian Kęska
      */

    public function addLink($link, $parser='')
    {
        if (!$this->panthera->types->validate($link, 'url'))
            return False;
            
        // if parser is none, try to detect
        if ($parser == '')
        {
            $data = $this->panthera->get_filters('cloned_parsers', $link);
            
            if (is_array($data))
            {
                $parser = $data['plugin'];
                $options = $data['options'];
            }
        }
        
        // parser does not exists
        if (!class_exists('cloned_' .$parser))
            return False;
            
        // manual selection using arg $parser
        if (!isset($data))
        {
            $p = "cloned_".$parser;
            $options = $p::$defaults;
        }
            
        $this->links[] = array('link' => $link, 'parser' => $parser, 'options' => $options);
        
        return True;
    }   
    
    /**
      * Get all links
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function getLinks()
    {
        return $this->links;
    }
    
    /**
      * Set option field
      *
      * @param string $link URL address of added link
      * @param string $key Key to set value 
      * @param mixed $value
      * @return bool
      * @author Damian Kęska
      */
    
    public function setField($link, $key, $value)
    {
        $index = False;
    
        // find link's index in array
        foreach ($this->links as $k => $a)
        {
            if ($a['link'] == $link)
            {
                $index = $k;
                break;
            }
        }

        // if link was not found
        if (is_bool($index))
            return False;
        
        // if option does not exists for selected link
        if (!array_key_exists($key, $this->links[$index]['options']))
            return False;

        // set new value
        $this->links[$index]['options'][$key] = $value;
        return True;
    }
    
    /**
      * Dump data to restore eg. after page refresh
      *
      * @return string 
      * @author Damian Kęska
      */
    
    public function dump()
    {
        return serialize(array('links' => $this->links, 'results' => $this->results, 'offset' => $this->offset));
    }
    
    /**
      * Restore serialized data
      *
      * @param string name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function restore($data)
    {
        $array = unserialize($data);
        
        if (is_array($array))
        {
            $this->links = $array['links'];
            return True;
        }
        
        return False;
    }
    
    /**
      * Run cloned jobs, every link will be parsed with selected parser
      *
      * @param int $offset Position to start from
      * @param int $limit Limit execution to $limit number of links
      * @return bool 
      * @author Damian Kęska
      */
    
    public function run($offset=0, $limit=0)
    {
        $i=$offset; // position
        $e=0; // execution count
        
        foreach ($this->links as $index => $item)
        {
            $i++; $e++;
            $f = "cloned_".$item['parser'];

            if (!class_exists($f))
                continue;
            
            try {
                $o = new $f($item['link']);
                $o -> setOptions($item['options']);
                $this->results[$index] = array('status' => 'success', 'result' => $o->parse());
                
            } catch (Exception $e) {
                $this->results[$index] = array('status' => 'failed', 'message' => $e->getMessage());
                continue;
            }
        }
        
        $this->offset = $i;
        
        return True;
    }
    
    /**
      * Return results
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function getResults()
    {
        return $this->results;
    }
}

/**
  * Abstract class for cloned plugins
  *
  * @package Panthera\modules\cloned
  * @author Damian Kęska
  */

abstract class cloned_plugin
{
    protected $options = array(), $link = "", $results = array();
    public static $defaults = array();
    
    public function parse() { return ''; }
    
    public function __construct($link)
    {
        $this->link = $link;
        $this->options = self::$defaults;
    }
    
    /**
      * Set configuration options
      * Returns True if $options argument is an array
      *
      * @param array $options
      * @return bool 
      * @author Damian Kęska
      */
    
    public function setOptions ($options)
    {
        if (is_array($options))
        {
            $this->options = $options;
            return True;
        }
        
        return False;
    }
}

/**
  * Extracting images from HTML content
  *
  * @package Panthera\modules\cloned
  * @author Damian Kęska
  */

class cloned_images extends cloned_plugin
{
    // configuration options
    //private $options = array('min-width' => 0, 'max-width' => 0, 'min-height' => 0, 'max-height' => 0, 'extension' => '*', 'name_contains' => '', 'width' => 0, 'height' => 0);
    public static $defaults = array('min-width' => -1, 'max-width' => -1, 'min-height' => -1, 'max-height' => -1, 'extension' => '*', 'name_contains' => '', 'width' => -1, 'height' => -1);

    public static function detect($link)
    {
        return array('plugin' => 'images', 'options' => self::$defaults);
    }
    
    /**
      * Search for images in HTML code
      *
      * @param bool $byPassCache
      * @return array 
      * @author Damian Kęska
      */
    
    public function parse($byPassCache=False)
    {
        global $panthera;
    
        // return from cache
        if (count($this->results) > 0 and $byPassCache == False)
            return $this->results;
    
        // download link data
        $options = array( 
          'http'=>array( 
            'method'=>"GET", 
              'timeout' => 10 
              ) 
        ); 
        
        $context = stream_context_create($options); 
        $HTML = file_get_contents($this->link, false, $context);
        
        // informations about url
        $url = parse_url($this->link);
        
        // default scheme
        if (!array_key_exists('scheme', $url))
            $url['scheme'] = 'http';
            
        // convert extension to lowercase and put into array
        if ($this->options['extension'] != '*')
        {
            $this->options['extension'] = str_replace(' ', '', strtolower($this->options['extension'])); // remove whitespaces
            $this->options['extension'] = explode(',', $this->options['extension']); // make an array by splitting string with "," separator
        }
        
        // check if we need to download a file to check if its valid
        $requiresDownload = False;
        
        if ($this->options['min-width'] != -1 or $this->options['max-width'] != -1 or $this->options['width'] != -1 or $this->options['height'] != -1 or $this->options['min-height'] != -1 or $this->options['max-height'] != -1)
        {
            $requiresDownload = True;
            //$tmpDir = maketmp();
        }
    
        // so... lets parse the document
        $pq = phpQuery::newDocument($HTML);
        $images = pq('img');
        
        foreach ($images as $value)
        {
            $src = pq($value)->attr('src');
            $lowerSrc = strtolower($src);
            $srcParsed = parse_url($src);
            
            // if domain not specified in image src, add it from page link
            if (!array_key_exists('host', $srcParsed))
            {
                $src = $url['scheme']. '://' .$url['host']. '/' .$src;
            }
            
            /* OPTIONS */
            
            // name contains option
            if ($this->options['name_contains'] != '')
            {
                // condition not met
                if (!strstr($src, $this->options['name_contains']))
                    continue;
            }
            
            // extension match eg. png or png,jpg,gif
            if ($this->options['extension'] != '*')
            {
                $pathinfo = pathinfo($srcParsed['path']);
                
                if (!in_array(strtolower($pathinfo['extension']), $this->options['extension']))
                    continue;
            }
            
            /* OPTIONS WITH REQUIRED DOWNLOAD */
            if ($requiresDownload == True)
            {
                $httplib = new httplib;
                
                try {
                    $httplib->timeout = 3;
                    $response = httplib::request($src);
                    $image = new SimpleImage();
                    $image -> loadFromString($response->body);

                    $width = $image -> getWidth();
                    $height = $image -> getHeight();
                                        
                    
                } catch (Exception $e) { 
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Timeout');
                    continue;
                }
                
                /* OPTIONS WITH IMAGE SIZE */
                
                // width, min-width, max-width
                if ($this->options['width'] != -1 and $width != $this->options['width'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'width');
                    continue; // doesnt match
                }
                    
                if ($this->options['min-width'] != -1 and $width < $this->options['min-width'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-width');
                    continue; 
                }

                if ($this->options['max-width'] != -1 and $width > $this->options['max-width'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-width');
                    continue;
                }
                    
                // height, min-width, max-width
                if ($this->options['height'] != -1 and $height != $this->options['height'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'height');
                    continue;
                }
                    
                if ($this->options['min-height'] != -1 and $height < $this->options['min-height'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-height');
                    continue;
                }
                    
                if ($this->options['max-height'] != -1 and $height > $this->options['max-height'])
                {
                    $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-height');
                    continue;
                }
                    
                $this->results[] = array('status' => 'success', 'data' => $src);
            }
        }
        
        return $this->results;
    }
}

$panthera -> add_option('cloned_parsers', array('cloned_images', 'detect'));

/* SOME TESTS */
/*$test = new cloned();
$test -> addLink('http://kwejk.pl/', 'images');
$test -> setField('http://kwejk.pl/', 'min-width', 100);

//var_dump($test->getLinks());

$test -> run();*/

/*print_r($test->getResults());*/
