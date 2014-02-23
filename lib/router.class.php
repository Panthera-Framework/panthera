<?php
/**
 * Application routing class
 * 
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

class routing {

    protected $routes = array();
    protected $namedRoutes = array();
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
    
    /**
     * Get routing cache
     * 
     * @return array
     */
    
    public function getCache()
    {
        $data = $this -> panthera -> config -> getKey('routing.cache');
        
        if ($data)
        {
            $this -> routes = $data['routes'];
            $this -> namedRoutes = $data['namedRoutes'];
            $this -> compiledRegexes = $data['compiledRegexes'];
        }
        
        return $data;
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
            'namedRoutes' => $this->namedRoutes,
            'compiledRegexes' => $this->compiledRegexes,
        );
        
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

        if($name) 
        {
            $this->namedRoutes[$name] = $route;
        }
        
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
            unset($this->namedRoutes[$name]);
            unset($this->compiledRegexes[$route]);
            
            $this->saveCache();
            
            return True;
        }
    }

    /**
     * Reversed routing
     *
     * Generate the URL for a named route. Replace regexes with supplied parameters
     *
     * @param string $routeName The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     * @return string The URL of the route with named parameters in place.
     */
    public function generate($routeName, array $params = array()) 
    {
        // Check if named route exists
        if(!isset($this->namedRoutes[$routeName])) 
        {
            throw new \Exception("Route '{$routeName}' does not exist.");
        }

        // Replace named parameters
        $route = $this->namedRoutes[$routeName];

        // prepend base path to route url again
        $url = $this->basePath . $route;

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER))
        {
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
                $match = @preg_match($regex, $requestUrl, $params);
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
                
                return array(
                    'target' => $target,
                    'params' => $params,
                    'name' => $name,
                    'methods' => explode('|', $this->routes[$name][0]),
                );
            }
        }

        $this -> panthera -> logging -> output('Routing resolved, found ' .intval($matches). ' matches', 'routing');

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
        
        $this->compiledRegexes[$originalRoute] = "`^$route$`";
        $this -> saveCache();
        return "`^$route$`";
    }
}
