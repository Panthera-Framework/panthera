<?php
/**
 * phpDocumentator XML template parsing and inserting to database
 * This module allows parsing phpDocumentator XML code to PHP's array that can be inserted to database
 * 
 * @package Panthera\core\components\docs
 * @author Damian Kęska
 * @license LGPLv3
 */
 
/** 
 * phpDocumentator XML template parser
 * This module allows parsing phpDocumentator XML code to PHP's array that can be inserted to database
 *
 * @package Panthera\core\components\docs
 * @author Damian Kęska
 */

class phpDocsParser
{
    public $packages = array();
    public $tags = array();
    public $contributors = array();
    public $files = array();
    
    /**
     * Constructor
     * 
     * @return null
     */
    
    public function __construct($document)
    {
        if (is_file($document))
            $xml = file_get_contents($document);
        else
            $xml = $document;
        
        $this -> dom = new DOMDocument;
        $this -> dom -> loadXML($xml);
        
        $this -> tags = array(
            $this -> dom -> documentElement -> childNodes,
            $this -> dom -> getElementsByTagName('property'),
            $this -> dom -> getElementsByTagName('class'),
            $this -> dom -> getElementsByTagName('method'),
            $this -> dom -> getElementsByTagName('function'),
            $this -> dom -> getElementsByTagName('constant'),
            $this -> dom -> getElementsByTagName('package'),
        );
    }
    
    /**
     * Add a package to list
     * 
     * @param DOMElement $tag Tag object
     * @return null
     */
    
    protected function addPackage($tag)
    {
        if ($tag instanceof DOMElement)
        {
            $package = $tag -> getAttribute('package');
            
            if (!$package)
                $package = $tag -> getAttribute('full_name');
                
            if (!$package or $package == 'global')
                return false;
                
            if (!isset($this -> packages[$package]))
                $this -> packages[$package] = array($tag -> tagName => 1);
            else {
                if (!isset($this -> packages[$package][$tag -> tagName]))
                    $this -> packages[$package][$tag -> tagName] = 0;
                
                $this -> packages[$package][$tag -> tagName]++;
            }
        }
    }
    
    /**
     * Get list of all packages
     * 
     * @return array
     */
    
    public function findPackages()
    {
        foreach ($this -> tags as $type)
        {
            foreach ($type as $tag)
                $this -> addPackage($tag);
        }
        
        ksort($this -> packages);
        
        return $this -> packages;
    }
    
    /**
     * Build list of contributions
     * 
     * @return array
     */
    
    public function buildContributionStats()
    {
        foreach ($this -> dom -> getElementsByTagName('tag') as $tag)
        {
            if ($tag -> getAttribute('name') == 'author')
            {
                if (!isset($this -> contributors[$tag -> getAttribute('description')]))
                    $this -> contributors[$tag -> getAttribute('description')] = 0;
                
                $this -> contributors[$tag -> getAttribute('description')]++;
            }
        }
        
        return $this -> contributors;
    }
    
    /**
     * Turn XML code into array that can be easily inserted to database or dumped into json oraz serialized
     * 
     * @return array
     */
    
    public function parseCode()
    {
        foreach ($this -> dom -> getElementsByTagName('file') as $file)
        {
            $fileName = $file -> getAttribute('path');
            
            $this -> files[$fileName] = array(
                'classes' => array(),
                'functions' => array(),
                'package' => $file -> getAttribute('package'),
            );
            
            // parse classes and it's attributes, methods
            foreach ($file -> getElementsByTagName('class') as $tag)
            {
                if (!$tag -> getElementsByTagName('name') -> item(0) -> nodeValue)
                    continue;
                
                $className = $tag -> getElementsByTagName('name') -> item(0) -> nodeValue;
                
                $this -> files[$fileName]['classes'][$className] = array(
                    'final' => $tag -> getAttribute('final'),
                    'abstract' => $tag -> getAttribute('abstract'),
                    'namespace' => $tag -> getAttribute('namespace'),
                    'line' => $tag -> getAttribute('line'),
                    'package' => $tag -> getAttribute('package'),
                    'properties' => array(),
                    'methods' => array(),
                    'extends' => $tag -> getElementsByTagName('extends') -> item(0) -> nodeValue,
                );
                
                // class properites
                foreach ($tag -> getElementsByTagName('property') as $property)
                {
                    $this -> files[$fileName]['classes'][$className]['properties'][$property -> getElementsByTagName('name') -> item(0) -> nodeValue] = array(
                        'default' => $property -> getElementsByTagName('default') -> item(0) -> nodeValue,
                        'static' => $property -> getAttribute('static'),
                        'visibility' => $property -> getAttribute('visibility'),
                        'line' => $property -> getAttribute('line'),
                        'package' => $property -> getAttribute('package'),
                        'description' => $property -> getElementsByTagName('docblock') -> item(0) -> getElementsByTagName('description') -> item(0) -> nodeValue,
                        'longdescription' => $property -> getElementsByTagName('docblock') -> item(0) -> getElementsByTagName('long-description') -> item(0) -> nodeValue,
                    );
                }
    
                foreach ($tag -> getElementsByTagName('method') as $method)
                    $this -> files[$fileName]['classes'][$className]['methods'][$method -> getElementsByTagName('name') -> item(0) -> nodeValue] = $this -> __parseFunction($method, $className);
            }

            foreach ($file -> getElementsByTagName('function') as $function)
                $this -> files[$fileName]['functions'][$function -> getElementsByTagName('name') -> item(0) -> nodeValue] = $this -> __parseFunction($function);
        }
    }

    public function __parseFunction($method, $className='')
    {
        $authors = array();
        $tags = array();
        $params = array();
        $return = array(
            'type' => '',
            'description' => '',
        );
        
        $features = array();
        $config = array();
                    
        foreach ($method -> getElementsByTagName('tag') as $t)
        {
            // @author
            if ($t -> getAttribute('name') == 'author')
            {
                $authors[] = $t -> getAttribute('description');
                continue;
                
            // @return
            } elseif ($t -> getAttribute('name') == 'return') {
                $return = array(
                    'type' => $t -> getAttribute('type'),
                    'description' => $t -> getAttribute('description'),
                );
                
                continue;
                
            // @feature, @hook (Panthera specific)
            } elseif ($t -> getAttribute('name') == 'feature' or $t -> getAttribute('name') == 'hook') {
                $z = $this -> __parseFeature($t -> getAttribute('description'));
                $features[$z['name']] = $z;
                continue;
            
            // @config (Panthera specific)
            } elseif ($t -> getAttribute('name') == 'config') {
                $t = explode(' ', $t -> getAttribute('description'));
                
                if (count($t) < 2)
                    $config[$t[0]] = '';
                else
                    $config[$t[1]] = $t[0];
                
                continue;
            
            // @param
            } elseif($t -> getAttribute('name') == 'param') {
                        
                $params[$t -> getAttribute('variable')] = array(
                    'line' => $t -> getAttribute('line'),
                    'description' => $t -> getAttribute('description'),
                    'type' => explode('|', $t -> getAttribute('type')),
                );
                            
                continue;
            }
            
            $tags[] = array(
                'name' => $t -> getAttribute('name'),
                'value' => $t -> getAttribute('description'),
            );
        }
        
        return array(
            'class' => $className,
            'config' => $config,
            'features' => $features,
            'return' => $return,
            'type' => $method -> tagName,
            'final' => $method -> getAttribute('final'),
            'abstract' => $method -> getAttribute('abstract'),
            'static' => $method -> getAttribute('static'),
            'visibility' => $method -> getAttribute('visibility'),
            'namespace' => $method -> getAttribute('namespace'),
            'line' => $method -> getAttribute('line'),
            'package' => $method -> getAttribute('package'),
            'authors' => $authors,
            'tags' => $tags,
            'description' => $method -> getElementsByTagName('docblock') -> item(0) -> getElementsByTagName('description') -> item(0) -> nodeValue,
            'longdescription' => $method -> getElementsByTagName('docblock') -> item(0) -> getElementsByTagName('long-description') -> item(0) -> nodeValue,
            'params' => $params,
        );
    }

    public function __parseFeature($text)
    {
        $text = str_replace('&amp;', '&', $text);
        
        $exp = explode(' ', $text);
        $info = array(
            'name' => $exp[0],
            'args' => array(),
            'description' => '',
            'reference' => false,
        );
        
        unset($exp[0]);
        
        foreach ($exp as $i => $block)
        {
            if (substr($block, 0, 1) === '(')
            {
                if (strpos($block, '&') !== False)
                {
                    $block = str_replace('&', '', $block);
                    $info['reference'] = true;
                }
                
                $z = explode(')', substr($block, 1, strlen($block)));
                
                $info['args'][$z[1]] = $z[0];
                unset($exp[$i]);
                continue;
            } elseif (substr($block, 0, 1) === '$' or substr($block, 0, 1) === '&') {
                if (strpos($block, '&') !== False)
                {
                    $block = str_replace('&', '', $block);
                    $info['reference'] = true;
                }
                
                $info['args'][$block] = null;
                unset($exp[$i]);
                continue;
            }
        }
        
        $info['description'] = implode(' ', $exp);
        return $info;
    }
}

/**
 * Inserts parsed documentation to database
 * 
 * @package Panthera\core\components\docs
 * @author Damian Kęska
 */

class phpDocsDB
{
    /**
     * Flatten phpDocsParser output
     * 
     * @param phpDocsParser $parser phpDocsParser object
     * @param string $branchName Branch name
     * @return array
     */
    
    public static function flattenArrays($parser, $branchName='master')
    {
        $functions = array(); // functions and methods
        $classes = array();
        $files = array();
        $packages = array();
        
        // find files, classes, functions and insert to flat arrays
        foreach ($parser -> files as $fileName => $f)
        {
            $f['path'] = $fileName;
            
            $files[] = array(
                'hashid' => md5($f['path'].$branchName),
                'path' => $f['path'],
                'package' => $f['package'],
                'branch' => $branchName,
            );
            
            // functions
            foreach ($f['functions'] as $name => $function)
            {
                $function['hashid'] = md5($name.$function['class'].$f['path']);
                $function['name'] = $name;
                $function['features'] = serialize($function['features']);
                $function['authors'] = serialize($function['authors']);
                $function['params'] = serialize($function['params']);
                $function['tags'] = serialize($function['tags']);
                $function['return'] = serialize($function['return']);
                $function['config'] = serialize($function['config']);
                $function['file'] = $f['path'];
                $function['branch'] = $branchName;
                $functions[] = $function;
            }
            
            foreach ($f['classes'] as $name => $class)
            {
                $class['hashid'] = md5($name.$f['path']);
                $class['name'] = $name;
                $class['properties'] = serialize($class['properties']);
                $class['file'] = $f['path'];
                $class['branch'] = $branchName;
                
                foreach ($class['methods'] as $name => $function)
                {
                    $function['hashid'] = md5($f['path'].$name.$function['class']);
                    $function['name'] = $name;
                    $function['features'] = serialize($function['features']);
                    $function['authors'] = serialize($function['authors']);
                    $function['params'] = serialize($function['params']);
                    $function['tags'] = serialize($function['tags']);
                    $function['config'] = serialize($function['config']);
                    $function['return'] = serialize($function['return']);
                    $function['file'] = '';
                    $function['branch'] = $branchName;
                    $functions[] = $function;
                }
                
                unset($class['methods']);
                $classes[] = $class;
            }
        }

        foreach ($parser -> packages as $package => $count)
        {
            $countOverall = 0;
            
            foreach ($count as $c)
                $countOverall += $c;
            
            $packages[] = array(
                'hashid' => md5($package.$f['path'].$branchName),
                'name' => $package,
                'stats' => serialize($count),
                'count' => $countOverall,
                'branch' => $branchName,
            );
        }

        return array(
            'packages' => $packages,
            'functions' => $functions,
            'classes' => $classes,
            'files' => $files,
        );
    }

    /**
     * Update documentation database
     * 
     * @static
     * @param string $file File path
     * @param string $branchName GIT branch name or group
     * @return bool
     */

    public static function updateDatabase($file, $branchName='master')
    {
        $panthera = pantheraCore::getInstance();
        $parser = new phpDocsParser($file);
        $parser -> parseCode();
        $parser -> findPackages();
        
        $panthera -> logging -> output('Flatting arrays', 'phpDocsDB');
        $array = static::flattenArrays($parser, $branchName);
        unset($parser);
        
        $where = array(
            'branch' => $branchName,
        );
        
        // remove data from all tables
        foreach ($array as $tableName => $data)
        {
            $panthera -> logging -> output('Updating "' .$tableName. '" with ' .count($data). ' records', 'phpDocsDB');
            $panthera -> db -> query('DELETE FROM `{$db_prefix}docs_' .$tableName. '` WHERE `branch` = :branch', $where);
            
            //foreach ($data as $row)
            //    $panthera -> db -> insert('docs_' .$tableName, $row);
            $panthera -> db -> insert('docs_' .$tableName, $data, True);
        }
        
        return True;
    }
}

/**
 * Documentation function object representation
 *
 * @package Panthera\core\components\docs
 * @author Damian Kęska 
 */

class docsFunction extends pantheraFetchDB
{
    protected $_tableName = 'docs_functions';
    protected $_idColumn = 'hashid';
    protected $_constructBy = array('hashid', 'array');
    protected $_removed = true; // read-only
    
    /**
     * Unserialize, convert to bools, ints etc. just correct types stored in database
     * 
     * @param string $key Database column name
     * @return mixed
     */
    
    public function __get($key)
    {
        if (in_array($key, array('features', 'authors', 'params', 'tags', 'config', 'return')))
            return unserialize($this -> _data[$key]);
        elseif (in_array($key, array('final', 'abstract', 'static')))
            return (bool)intval($this -> _data[$key]);
        elseif ($key == 'line')
            return intval($this -> _data[$key]);
        
        return parent::__get($key);
    }
    
    /**
     * Get return in text format
     * Normally "return" is in array format, this function converts it to string with "|" separators
     * 
     * @return string
     */
    
    public function getTextReturn()
    {
        return trim(implode('|', $this -> return), '|');
    }

    /**
     * Return params as string
     * 
     * @return string
     */

    public function getParamsText()
    {
        $str = '';
        
        foreach ($this -> params as $key => $param)
        {
            if ($param['type'])
                $str .= $parm['type']. ' ';
            
            $str .= $key. ', ';
        }
        
        return trim($str, ', ');
    }
}

/**
 * Documentation file object representation
 *
 * @package Panthera\core\components\docs
 * @author Damian Kęska 
 */

class docsFile extends pantheraFetchDB
{
    protected $_tableName = 'docs_file';
    protected $_idColumn = 'hashid';
    protected $_constructBy = array('hashid', 'path', 'array');
    protected $_removed = true; // read-only
}

/**
 * Documentation packages object representation
 *
 * @package Panthera\core\components\docs
 * @author Damian Kęska 
 */

class docsPackage extends pantheraFetchDB
{
    protected $_tableName = 'docs_packages';
    protected $_idColumn = 'hashid';
    protected $_constructBy = array('hashid', 'name', 'array');
    protected $_removed = true; // read-only
    
    /**
     * Get all class methods
     * 
     * @return array Array of docsFunction objects 
     */
    
    public function getClasses()
    {
        $items = docsClass::fetchAll(array(
            'package' => $this -> name,
        ));
        
        foreach ($items as $i => $item)
        {
            $items[$items->name] = $item;
            unset($items[$i]);
        }
        
        return $items;
    }
}

/**
 * Documentation packages object representation
 *
 * @package Panthera\core\components\docs
 * @author Damian Kęska 
 */

class docsClass extends pantheraFetchDB
{
    protected $_tableName = 'docs_classes';
    protected $_idColumn = 'hashid';
    protected $_constructBy = array('hashid', 'name', 'array');
    protected $_removed = true; // read-only
    
    /**
     * Unserialize, convert to bools, ints etc. just correct types stored in database
     * 
     * @param string $key Database column name
     * @return mixed
     */
    
    public function __get($key)
    {
        if ($key == 'properties')
            return unserialize($this -> _data[$key]);
        elseif (in_array($key, array('final', 'abstract', 'namespace')))
            return (bool)intval($this -> _data[$key]);
        elseif ($key == 'line')
            return intval($this -> _data[$key]);
        
        return parent::__get($key);
    }
    
    /**
     * Get all class methods
     * 
     * @return array Array of docsFunction objects 
     */
    
    public function getMethods()
    {
        $methods = docsFunction::fetchAll(array(
            'class' => $this -> name,
            'package' => $this -> package,
        ));
        
        foreach ($methods as $i => $method)
        {
            $methods[$method->name] = $method;
            unset($methods[$i]);
        }
        
        return $methods;
    }
}
