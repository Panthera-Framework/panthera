<?php

/**
  * WWW server log parser
  *
  * @package Panthera\modules\accessparser
  * @author Mateusz Warzyński
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

 /**
  * Simple class to get access log content
  *
  * @package Panthera\modules\accessparser
  * @author Mateusz Warzyński
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

class accessParser
{
    protected $lineArray = array();
    protected $matches = array();
    
     /**
      * Read log from file and return it as array (by lines)
      *
      * @param $linesCount amount of lines that will be read
      * @return bool/array
      * @author Mateusz Warzyński
      * @author Damian Kęska
      */
    
    public function readLog($linesCount=500)
    {
        global $panthera;
        
        $path = $panthera -> config -> getKey('path_to_server_log', '/var/www/localhost/htdocs/other/lighttpd_log', 'string');

        $fp = fopen($path, "r");

        $buffer = '';
        $bufferSize = 1024;
        $position = filesize($path);
        $data = '';

        $n = 0;
        while ($n < $linesCount/4) // in 1024 bytes are more lines than only one (1/4 -> 114) | (1 -> 440) with $linesCount = 100
        {                          // checked using lighttpd log - estimate number may be different in case of other servers 
                $position = $position - $bufferSize;
                fseek($fp, $position);
                $buffer = fread($fp, $bufferSize);
                $data = $buffer.$data;
                
                // check if there is a line
                if (strpos($buffer, "\n") !== False) {
                    $n++;
                } elseif ($buffer == '') {
                    $linesCount = $n;
                }
        }
        
        $lines = explode("\n", $data);

        for ($i = 1; $i <= count($lines)-2; $i++)
            $this->lineArray[] = $lines[$i];

        $this->lineArray = array_reverse($this->lineArray); // because of line 49 we must reverse array
        
        // execute function to parse log
        if (!$this -> parseLog())
            return $this->matches;
        else
            return true;
    }

    /**
      * Parse log (line by line)
      *     notice that lineArray must be defined
      * 
      * @return bool
      * @author Mateusz Warzyński
      */

    protected function parseLog()
    {
        global $panthera;
        
        if (!count($this->lineArray))
            return false;
            
        if ($panthera->cache and $panthera->cache->exists('parsedAccessLog'))
        {
            $results = $panthera -> cache -> get('parsedAccessLog');
            $lastCachedLine = $results[0];
        } else {
            $lastCachedLine = array();
        }
        
        $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';
        
        foreach ($this->lineArray as $number => $line) {
            preg_match($regex , $line, $matches);
            $this->matches[$number]['client_address'] = $matches[2];
            $this->matches[$number]['date'] = $matches[4];
            $this->matches[$number]['time'] = $matches[5];
            $this->matches[$number]['processing_request_time'] = $matches[6];
            $this->matches[$number]['http_method'] = $matches[7];
            $this->matches[$number]['url_request'] = $matches[8];
            $this->matches[$number]['protocol'] = $matches[9];
            $this->matches[$number]['status'] = $matches[10];
            $this->matches[$number]['response_size'] = $matches[11]; // in bytes
            $this->matches[$number]['referer'] = $matches[12];
            $this->matches[$number]['browser_headers'] = $matches[13];
            
            if ($this->matches[$number] == $lastCachedLine) // just to be precise
                break;
        }
        
        // clear memory
        unset($matches);
        unset($regex);

        if ($panthera->cache)
        {
            if (isset($results))
                $results = array_merge($this->matches, $results);
            else
                $results = $this->matches;
            
            $panthera -> cache -> set('parsedAccessLog', $results, 86400);
            unset($results);
            
            return true;
            
        } else {
            $panthera -> logging -> output('Error. Cannot get access to cache.', 'accessparser');
            return false;
        }
    }
}