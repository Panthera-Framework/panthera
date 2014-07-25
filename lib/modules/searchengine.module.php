<?php
/**
 * Panthera Framework simple search engine
 * 
 * Performing a search inside of Panthera modules and returns results in universal way (like a Google or Bing web search)
 * 
 * Example:
 * <code>
 * $search = new search;
 * $search -> setModules(array('users', 'config'));
 * var_dump($search -> query('a', 3)); // show 3th page of searching for "a"
 * </code>
 * 
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Panthera Framework simple search engine
 * 
 * Performing a search inside of Panthera modules and returns results in universal way (like a Google or Bing web search)
 * 
 * Example:
 * <code>
 * $search = new search;
 * $search -> setModules(array('users', 'config'));
 * var_dump($search -> query('a', 3)); // show 3th page of searching for "a"
 * </code>
 * 
 * @author Damian Kęska
 */
 
class search
{
    protected $modules = array();
    public $resultsOnPage = 10;
    
    /**
     * Custom module space
     * 
     * @var $moduleSpaces
     * @author Damian Kęska
     */
    
    public $moduleSpaces = array(
    
    );
    
    /**
     * Import search engine modules
     * 
     * @param array $array List of modules
     * @author Damian Kęska
     * @return null
     */
    
    public function setModules($array)
    {
        $this -> modules = array(); // reset modules
        
        foreach ($array as $module)
        {
            if (panthera::getInstance() -> moduleExists('searchengine/' .$module))
            {
                panthera::getInstance() -> importModule('searchengine/' .$module);
                $c = basename($module). 'Searchengine';
                $this -> modules[$module] = new $c;
            } else
                panthera::getInstance() -> logging -> output('Module searchengine/' .$module. ' does not exists, skipping this search engine', 'search');
        }
    }
    
    /**
     * Perform a query
     * 
     * @param string $query Query string
     * @param int $page Page number
     * @param string $queryType (Optional) Query type eg. "resultsCount", "normal", "pagesCount"
     * @author Damian Kęska
     * @return array
     */
    
    public function query($query, $page=1, $queryType='normal')
    {
        $panthera = panthera::getInstance();
        $page--;
        $space = $this -> resultsOnPage; // we have only X items per page, there is no enough room for more results
        $spacePerModule = ceil($this -> resultsOnPage / count($this -> modules));
        $modulePages = array();
        $results = array();
        $totalCount = 0;
        $totalPages = 0;
        
        $panthera -> logging -> output('Preparing to query ' .count($this -> modules). ' to get results for ' .($page+1). ' page', 'search');
        
        // TODO: A quick cache
        
        // calculate results count
        foreach ($this -> modules as $moduleName => $module)
        {
            $count = $module -> query($query, False);
            $totalCount += $count;
            
            // module spaces
            $moduleSpace = $spacePerModule;
            
            if (isset($this -> moduleSpaces[$moduleName]))
                $moduleSpace = $this -> moduleSpaces[$moduleName];
            
            $modulePages[$moduleName] = array(
                'count' => $count,
                'pages' => ceil((intval($count) / $moduleSpace)),
                'results' => array(),
                'offset' => 0,
                'limit' => $moduleSpace,
            );
            
            if ($totalPages < $modulePages[$moduleName]['pages'])
                $totalPages = $modulePages[$moduleName]['pages'];
            
            $panthera -> logging -> output('Got ' .$count. ' results from ' .$moduleName, 'search');
        }
        
        
        if ($queryType == 'resultsCount')
            return $totalCount;
        elseif ($queryType == 'pagesCount')
            return $totalPages;
        
        
        $freeSpace = 0;

        foreach ($this -> modules as $moduleName => $module)
        {
            // if we reached page count limit
            if ($page > $modulePages[$moduleName]['pages'])
            {
                $panthera -> logging -> output('Reached page count of ' .$moduleName. ' module, adding more free space', 'search');
                $freeSpace += $modulePages[$moduleName]['limit'];
                continue;
            }
            
            $limit = $modulePages[$moduleName]['limit'];
            
            // fill free space with results from other module
            if ($freeSpace)
            {
                $limit += $freeSpace;
                $freeSpace = 0;
            }
            
            $modulePages[$moduleName]['offset'] = ($modulePages[$moduleName]['limit']*$page);
            $panthera -> logging -> output('(' .$moduleName. ') Fetching rows offset ' .$modulePages[$moduleName]['offset']. ' (limit: ' .$limit. ')', 'search');
            $modulePages[$moduleName]['results'] = $module -> query($query, $modulePages[$moduleName]['offset'],  $limit);
            
            if (count($modulePages[$moduleName]['results']) < $limit)
                $freeSpace += ($limit-count($modulePages[$moduleName]['results']));
            
            // output to console for debugging
            $panthera -> logging -> output('Free space: ' .$freeSpace, 'search');
            
            
            if (!is_array($modulePages[$moduleName]['results']))
            {
                $panthera -> logging -> output('Invalid output results array from ' .$moduleName. ' module', 'search');
                continue;
            }
            
            $results = array_merge($results, $modulePages[$moduleName]['results']);
        }
        
        return array(
            'results' => $results,
            'pages' => intval($totalPages),
            'currentResults' => count($results),
        );
    }
}

$search = new search;
$search -> setModules(array('events', 'config', 'places'));
var_dump($search -> query('', 3));
