<?php
/**
 * Application routing class
 * 
 * @package Panthera\core\routing
 * @author Danny van Kooten
 * @author Koen Punt
 * @author John Long
 * @author Niahoo Osef
 * @author Damian Kęska
 * @license MIT License
 * 
 * Copyright (c) 2012-2013 Danny van Kooten hi@dannyvankooten.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 */
 
/**
 * Routing resolving, links management
 * 
 * @package Panthera\core\routing
 * @author Danny van Kooten
 * @author Koen Punt
 * @author John Long
 * @author Niahoo Osef
 * @author Damian Kęska
 */

class routing {

    protected $routes = array();
    protected $compiledRegexes = array();
    protected $basePath = '';
    protected $matchTypes = array(
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++'
    );
    protected $cacheType = 'varcache'; // varcache or config
    public $lastMatched = null;
    
    /**
     * Get routing cache
     * 
     * @return array
     */
    
    public function getCache()
    {
        $data = null;
        
        if ($this->cacheType == 'varcache' and $this -> panthera -> varCache)
            $data = $this -> panthera -> varCache -> get('routing.cache');
        
        if (!$data)
            $data = $this -> panthera -> config -> getKey('routing.cache');
        
        if ($data)
        {
            $this -> routes = $data['routes'];
            $this -> compiledRegexes = $data['compiledRegexes'];
        }
        
        return $data;
    }
    
    /**
     * Check if route exists
     * 
     * @param string $name Route name
     * @return bool
     */
    
    public function exists($name)
    {
        return isset($this->routes[$name]);
    }
    
    /**
     * Save routing cache
     * 
     * @return null
     */
    
    public function saveCache()
    {
        $data = array(
            'routes' => $this->routes,
            'compiledRegexes' => $this->compiledRegexes,
        );
        
        if ($this->cacheType == 'varcache' and $this -> panthera -> varCache)
            $this -> panthera -> varCache -> set('routing.cache', $data, -1); // infinite
        
        $this -> panthera -> config -> setKey('routing.cache', $data, 'array');
    }
    /**
      * Create router in one call from config.
      *
      * @param array $routes
      * @param string $basePath
      * @param array $matchTypes
      */
      
    public function __construct( $routes = array(), $basePath = '', $matchTypes = array() )
    {
        $this -> panthera = pantheraCore::getInstance();
        $this -> setBasePath($basePath);
        $this -> addMatchTypes($matchTypes);
        $this -> getCache();

        foreach( $routes as $route ) 
        {
            call_user_func_array(array($this,'map'),$route);
        }
    }

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     * 
     * @param $basePath Routing base path
     */
     
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Add named match types. It uses array_merge so keys can be overwritten.
     *
     * @param array $matchTypes The key is the name and the value is the regex.
     */
     
    public function addMatchTypes($matchTypes)
    {
        $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
    }

    /**
     * Map a route to a target
     *
     * @param string $method One of 4 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PUT|DELETE)
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Name of this route
     *
     */
    public function map($method='GET|POST', $route, $target, $name)
    {
        $this->routes[$name] = array($method, $route, $target, $name);
        $this->compileRoute($route);
        $this -> saveCache();
        return;
    }
    
    /**
     * Remove route from routing database
     * 
     * @param string $name Route name
     * @return bool
     */
    
    public function unmap($name)
    {
        if (isset($this->routes[$name]))
        {
            $route = $this->routes[$name][1];
            
            unset($this->routes[$name]);
            unset($this->compiledRegexes[$route]);
            
            $this->saveCache();
            
            return True;
        }
    }
    
    /**
     * Get list of routes
     * 
     * @author Damian Kęska
     * @return array
     */
    
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Get list of route parameters
     * 
     * @param string $routeName Name of the route
     * @author Damian Kęska
     * @throws Exception When route does not exists
     * @return array
     */
    
    public function getParams($routeName)
    {
        if(!isset($this->routes[$routeName])) 
        {
            throw new Exception("Route '{$routeName}' does not exist.");
        }
        
        $route = $this->routes[$routeName][1];
        $compiled = $this -> compileRoute($this->routes[$routeName][1]);
        $fields = array();
        
        if (isset($compiled['matches']))
        {
            $matches = $compiled['matches'];
            
            foreach($matches as $match)
            {
                list($block, $pre, $type, $param, $optional) = $match;
                $fields[] = $param;
            }
        }
        
        return $fields;
    }

    /**
     * Reversed routing
     *
     * Generate the URL for a named route. Replace regexes with supplied parameters
     *
     * @param string $routeName The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     * @param array|string $get Optional $_GET parameters
     * @return string The URL of the route with named parameters in place.
     */
    public function generate($routeName, array $params = array(), $get=null) 
    {
        // Check if route exists
        if(!isset($this->routes[$routeName])) 
        {
            throw new Exception("Route '{$routeName}' does not exist.");
        }

        // get parameters
        $route = $this->routes[$routeName][1];

        // prepend base path to route url again
        $url = $this->basePath . $route;
        
        $compiled = $this -> compileRoute($this->routes[$routeName][1]);

        if (isset($compiled['matches']))
        {
            $matches = $compiled['matches'];
            
            foreach($matches as $match)
            {
                list($block, $pre, $type, $param, $optional) = $match;
                
                if ($pre) 
                {
                    $block = substr($block, 1);
                }

                if(isset($params[$param])) 
                {
                    $url = str_replace($block, $params[$param], $url);
                } elseif ($optional) {
                    $url = str_replace($pre . $block, '', $url);
                }
            }
        }
        
        if ($get and is_string($get))
            parse_str($get, $get);
        
        if (!is_array($get))
            $get = array();
        
        /*if (isset($this->routes[$routeName][2]['GET']))
        {
            if (is_array($this->routes[$routeName][2]['GET']))
                $get = array_merge($get, $this->routes[$routeName][2]['GET']);
        }*/
        
        if (count($get))
        {
            if (!parse_url($url, PHP_URL_QUERY))
                $url .= '?';
            
            $url .= http_build_query($get);
        }
        
        return $url;
    }

    /**
     * Match a given Request Url against stored routes
     * @param string $requestUrl
     * @param string $requestMethod
     * @return array|boolean Array with route information on success, false on failure (no match).
     */
     
    public function resolve($requestUrl = null, $requestMethod = null) 
    {
        $this -> panthera -> logging -> startTimer();
        $params = array();
        $match = false;

        // set Request Url if it isn't passed as parameter
        if($requestUrl === null) 
        {
            $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) 
        {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        // set Request Method if it isn't passed as a parameter
        if($requestMethod === null) 
        {
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }

        // Force request_order to be GP
        // http://www.mail-archive.com/internals@lists.php.net/msg33119.html
        $_REQUEST = array_merge($_GET, $_POST);

        foreach($this->routes as $handler) 
        {
            list($method, $_route, $target, $name) = $handler;

            $methods = explode('|', $method);
            $method_match = false;

            // Check if request method matches. If not, abandon early. (CHEAP)
            foreach($methods as $method) {
                if (strcasecmp($requestMethod, $method) === 0) {
                    $method_match = true;
                    break;
                }
            }

            // Method did not match, continue to next route.
            if(!$method_match) continue;

            // Check for a wildcard (matches all)
            if ($_route === '*') {
                $match = true;
            } elseif (isset($_route[0]) && $_route[0] === '@') {
                $match = preg_match('`' . substr($_route, 1) . '`', $requestUrl, $params);
            } else {
                $route = null;
                $regex = false;
                $j = 0;
                $n = isset($_route[0]) ? $_route[0] : null;
                $i = 0;

                // Find the longest non-regex substring and match it against the URI
                while (true) {
                    if (!isset($_route[$i])) {
                        break;
                    } elseif (false === $regex) {
                        $c = $n;
                        $regex = $c === '[' || $c === '(' || $c === '.';
                        if (false === $regex && false !== isset($_route[$i+1])) {
                            $n = $_route[$i + 1];
                            $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                        }
                        if (false === $regex && $c !== '/' && (!isset($requestUrl[$j]) || $c !== $requestUrl[$j])) {
                            continue 2;
                        }
                        $j++;
                    }
                    $route .= $_route[$i++];
                }

                $regex = $this->compileRoute($route);
                $regex = $regex['regex'];
                $match = @preg_match($regex, $requestUrl, $params);
                
                if ($match === False)
                    $this -> panthera -> logging -> output('Something wrong with route "' .$route. '", regex: ' .$regex. '. preg_match returned error, please check route syntax', 'routing');
            }

            if(($match == true || $match > 0)) 
            {

                if($params) 
                {
                    foreach($params as $key => $value) 
                    {
                        if(is_numeric($key)) unset($params[$key]);
                    }
                }

                $this -> panthera -> logging -> output('Routing resolved, found match', 'routing');
                $this -> lastMatched = array(
                    'target' => $target,
                    'params' => $params,
                    'name' => $name,
                );
                
                return array(
                    'target' => $target,
                    'params' => $params,
                    'name' => $name,
                    'methods' => explode('|', $this->routes[$name][0]),
                );
            }
        }

        $this -> panthera -> logging -> output('Routing resolved, found ' .intval($match). ' matches', 'routing');

        return false;
    }

    /**
     * Compile the regex for a given route (EXPENSIVE)
     * 
     * @param string $route Route expression
     * @return string
     */
     
    private function compileRoute($route) 
    {
        if (isset($this->compiledRegexes[$route]))
        {
            return $this->compiledRegexes[$route];
        }
        
        $this -> panthera -> logging -> output('No compiled route found in cache for "' .$route. '"', 'routing');
        
        $originalRoute = $route;
        
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {

            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . '))'
                        . ($optional !== '' ? '?' : null);

                $route = str_replace($block, $pattern, $route);
            }

        }
        
        $this->compiledRegexes[$originalRoute] = array(
            'regex' => "`^$route$`",
            'matches' => $matches
        );
        
        $this -> saveCache();
        return "`^$route$`";
    }
}

/**
 * Alias to $panthera->routing->generate() (for use inside of templates)
 * 
 * @param string $routeName Route name
 * @param array $params Array of parameters
 * @param array|string $get Optional $_GET parameters
 * @package Panthera\core\routing
 * @return string
 */

function getRoute($routeName, $params, $get)
{
    $panthera = pantheraCore::getInstance();
    return $panthera -> routing -> generate($routeName, $params, $get);
}