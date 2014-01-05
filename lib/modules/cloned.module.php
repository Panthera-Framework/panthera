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
        
        // parser does not exist
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
  * @author Mateusz Warzyński
  */

class cloned_images extends cloned_plugin
{
    // configuration options
    //private $options = array('min-width' => 0, 'max-width' => 0, 'min-height' => 0, 'max-height' => 0, 'extension' => '*', 'name_contains' => '', 'width' => 0, 'height' => 0);
    public static $defaults = array('min-width' => -1, 'max-width' => -1, 'min-height' => -1, 'max-height' => -1, 'extension' => '*', 'name_contains' => '', 'width' => -1, 'height' => -1, 'save' => False, 'resize' => False);
    public $specialized = array('parse' => False, 'createImage' => False, 'getImages' => False, 'cropBottom' => False);
    private $allowedExtensions = array('.jpg', '.png', '.gif', '.jpeg');

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
      * @author Mateusz Warzyński
      */
    
    public function parse($byPassCache=False)
    {
        global $panthera;
    
        // return from cache
        if (count($this->results) > 0 and $byPassCache == False)
            return $this->results;
        
        if ($this->options['save'])
        {
            $uploadDir = pantheraUrl('{$upload_dir}/cloned/');
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777);
        }

        // get domain from link
        $parse = parse_url($this->link);
        $parse = explode('.', $parse['host']);
        $moduleName = $parse[count($parse)-2];
        $module = 'cloned/'.$moduleName;

        // use specified options for this link
        if ($panthera->importModule($module)) {
            $plugin = new $moduleName($this);
            $this->specialized = $plugin->getOptions();
            
            if ($this->specialized['parse']) {
                $plugin -> specifiedParse();
                return $this->results;   
            }
        }
        
        if (strpos($this->link, '.jpg') or strpos($this->link, '.png') !== false or strpos($this->link, '.gif') !== false or strpos($this->link, '.jpeg') !== false) {
            
            if ($this->specialized['createImage'])
                $plugin -> createImage($this->link);
            else
                $this -> createImage($this->link);
            
            return $this->results;
        }
        
        $requiresDownload = False;
        
        // check if we need to download a file to check if its valid
        if ($this->options['min-width'] != -1 or $this->options['max-width'] != -1 or $this->options['width'] != -1 or $this->options['height'] != -1 or $this->options['min-height'] != -1 or $this->options['max-height'] != -1)
            $requiresDownload = True; //$tmpDir = maketmp();
        
        
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
    
        // get images instances from htnl (<img src='' >)
        if ($this->specialized['getImages'])
            $pq = $plugin -> getImages();
        else
            $pq = $this -> getImages();
        
        $images = pq('img', $pq);
         
        foreach ($images as $value)
        {
            $src = pq($value, $pq)->attr('src');
            $lowerSrc = strtolower($src);
            $srcParsed = parse_url($src);
            
            // if domain not specified in image src, add it from page link
            if (!array_key_exists('host', $srcParsed))
            {
                $src = $url['scheme']. '://' .$url['host']. '/' .$src;
            }
            
            if ($src[0].$src[1].$src[2].$src[3] != 'http') {
                $src = 'http:'.$src;
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
            if ($requiresDownload == True) {
                if ($this->specialized['createImage'])
                    $plugin -> createImage($src);
                else
                    $this -> createImage($src);
            }
        }
        
        return $this->results;
    }

    /**
      * Create image from link, call other function to checkOptions and save
      *
      * @param $src of image 
      * @return bool
      * @author Mateusz Warzyński
      */

    private function createImage($src) 
    {
        $extension = strtolower(substr($src, -4));
        
        if (!array_key_exists($extension, $this->allowedExtensions))
            list($src, $extension) = $this->getExtension($src);
        
        // get type of image by extension
        if ($extension == '.jpg')
            $type = IMAGETYPE_JPEG;
        elseif ($extension == '.png')
            $type = IMAGETYPE_PNG;
        elseif ($extension == '.gif')
            $type = IMAGETYPE_GIF;
        else
            $type = IMAGETYPE_JPEG; // default imageType
            
        if ($type == IMAGETYPE_GIF) {
            $this -> saveGIF($src);
            return True;
        }
        
        $httplib = new httplib;
        
        try {
            $httplib->timeout = 3;
            $response = httplib::request($src);
            $image = new SimpleImage();
            $image -> loadFromString($response, $type);
            
            if ($this->specialized['cropBottom'] > 0)
                $image -> cropBottom(intval($this->specialized['cropBottom']));
            
            if ($this->options['resize']) {
                $imageResized = $this->resizeImage($image, $type);
                
                if ($imageResized != False) {
                    $image = $imageResized;
                    unset($imageResized);
                }
            }
            
            $width = $image->getWidth();
            $height = $image -> getHeight();
            
        } catch (Exception $e) { 
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Timeout');
            return False;
        }
        // check options (to return validate information and optionally save image)     
        $this -> checkOptions($width, $height, $src, $image);
    }

    /**
      * Get extension from link (sometimes there is some php values ?page=asdasd&picture=asdads)
      *
      * @return array ($link, $extension)
      * @author Mateusz Warzyński
      */

    private function getExtension($src)
    {
        // get extension from link
        if (strpos($src, '.jpg') !== false)
            $extension = '.jpg';
        elseif (strpos($src, '.png') !== false)
            $extension = '.png';
        elseif (strpos($src, '.gif') !== false)
            $extension = '.gif';
        elseif (strpos($src, '.jpeg') !== false)
            $extension = '.jpeg';
        else
            return False;
        
        // get link to image
        $link = explode($extension, $src);
        $src = $link[0].$extension;
        unset($link);
        
        // just to be sure...
        $src = str_replace('?', '', $src);
        
        return array($src, $extension);
    }
    
    /**
      * Get images instances from html content
      *
      * @return phpQuery object 
      * @author Mateusz Warzyński
      */

    private function getImages()
    {
        // download link data
        $options = array( 'http'=>array( 'method'=>"GET", 'timeout' => 10 ) );
        
        $context = stream_context_create($options); 
        $HTML = file_get_contents($this->link, false, $context);
        
        // so... lets parse the document
        return phpQuery::newDocument($HTML); 
    }

    /**
      * Check opitons for found image (optionally save)
      *
      * @param int $width
      * @param int $height
      * @param string $src, link to image
      * @param object $image, simpleimage object of image
      * @return void 
      * @author Mateusz Warzyński
      */

    private function checkOptions($width, $height, $src, $image)
    {
        // width, min-width, max-width
        if ($this->options['width'] != -1 and $width != $this->options['width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'width');
            return False; // doesn't match
        }
                    
        if ($this->options['min-width'] != -1 and $width < $this->options['min-width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-width');
            return False;
        }

        if ($this->options['max-width'] != -1 and $width > $this->options['max-width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-width');
            return False;
        }
                    
        // height, min-width, max-width
        if ($this->options['height'] != -1 and $height != $this->options['height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'height');
            return False;
        }
                    
        if ($this->options['min-height'] != -1 and $height < $this->options['min-height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-height');
            return False;
        }
                    
        if ($this->options['max-height'] != -1 and $height > $this->options['max-height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-height');
            return False;
        }
                
        // save file
        if ($this->options['save'])
            $this->save($image, $src, $extension);
        else
            $this->results[] = array('status' => 'success', 'data' => $src);   
        
        return True;
    }

    /**
      * Download image
      *
      * @param string $src to image
      * @return bool 
      * @author Mateusz Warzyński
      */

    private function save($image, $src, $extension)
    {
        switch ($image->image_type) {
            case IMAGETYPE_JPEG:
                $extension = '.jpg';
                break;
            case IMAGETYPE_PNG:
                $extension = '.png';
                break;
            case IMAGETYPE_GIF:
                $extension = '.gif';
                break;
        }
            
        $name = hash('md4', basename($src)).$extension;
        unset($extension);
                    
        if (strpos($name, '.php') === FALSE) {
            $uploadDir = pantheraUrl('{$upload_dir}/cloned/');
            $filePath = pantheraUrl($uploadDir.$name);
            $image -> save($filePath);
            $this->results[] = array('status' => 'success', 'data' => $src, 'path' => $filePath);
            return True;
        }
    }
    
    /**
      * Download animation
      *
      * @param string $src to image
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    private function saveGIF($src)
    {
        $name = hash('md4', basename($src)).'gif';
        
        if (strpos($name, '.php') === FALSE) {
            $uploadDir = pantheraUrl('{$upload_dir}/cloned/');
            $filePath = pantheraUrl($uploadDir.$name);
            file_put_contents($filePath, file_get_contents($src));
            
            if ($this->checkGIFOptions($filePath)) {
                $this->results[] = array('status' => 'success', 'data' => $src, 'path' => $filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    
    /**
      * Check opitons for found gif
      *
      * @param string $src
      * @return bool 
      * @author Mateusz Warzyński
      */

    private function checkGIFOptions($filePath)
    {
        $image = imagecreatefromgif($filePath);
    
        $width = imagesx($image);
        $height = imagesy($image);
        
        // width, min-width, max-width
        if ($this->options['width'] != -1 and $width != $this->options['width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'width');
            return False; // doesn't match
        }
                    
        if ($this->options['min-width'] != -1 and $width < $this->options['min-width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-width');
            return False;
        }

        if ($this->options['max-width'] != -1 and $width > $this->options['max-width'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-width');
            return False;
        }
                    
        // height, min-width, max-width
        if ($this->options['height'] != -1 and $height != $this->options['height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'height');
            return False;
        }
                    
        if ($this->options['min-height'] != -1 and $height < $this->options['min-height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'min-height');
            return False;
        }
                    
        if ($this->options['max-height'] != -1 and $height > $this->options['max-height'])
        {
            $this->results[] = array('data' => $src, 'status' => 'failed', 'code' => 'Filter_Mismatch', 'filter' => 'max-height');
            return False;
        }
                
        return True;
    }

    /**
      * Resize image to fit dimensions set in options
      *
      * @param object $image simpleimage
      * @return bool/object 
      * @author Mateusz Warzyński
      */
    
    private function resizeImage($image) {
        
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();
        
        if ($this->options['max-width'] != -1)
            $width = $this->options['max-width'];
            
        if ($this->options['min-width'] != -1 and $this->options['max-width'] == -1)
            $width = $this->options['min-width'];
        
        if ($this->options['width'] != -1)
            $widthImportant = $this->options['width'];
            
        if ($this->options['max-height'] != -1)
            $height = $this->options['max-height'];
            
        if ($this->options['min-height'] != -1 and $this->options['max-height'] == -1)
            $height = $this->options['min-height'];
        
        if ($this->options['height'] != -1)
            $heightImportant = $this->options['height'];
        
        // brake ratio and resize to given values
        if (isset($heightImportant) and isset($widthImportant)) {
            $image->resize($widthImportant, $heightImportant);
            unset($heightImportant); unset($widthImportant);
            return $image;
        } elseif (isset($heightImportant)) {
            $height = $heightImportant;
        } elseif (isset($widthImportant)) {
            $width = $widthImportant;
        }
        
        if ($width != -1 and !isset($height) and $width < $imageWidth) {
            $ratio = $width / $imageWidth;
            $image->resize($width, $imageHeight*$ratio);
            unset($ratio); return $image;
        } elseif (!isset($width) and $height != -1 and $height < $imageHeight) {
            $ratio = $height / $imageHeight;
            $image->resize($imageWidth*$ratio, $height);
            unset($ratio); return $image;   
        } else {
            // TODO: implement resizing images if isset only max-width and max-height (to get best quality of image)
            return $image;
        }
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
