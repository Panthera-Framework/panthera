<?php
/**
  * Google Page Rank checking class
  *
  * @package Panthera\modules\googlepr
  * @license GNU Affero General Public License 3, see license.txt
  * @see http://www.tutorialconnect.com/google-pagerank-checker-php-script/
  * @author Damian Kęska
  * @author tutorialconnect.com
  */

/**
  * Google Page Rank checking class
  *
  * @package Panthera\modules\googlepr
  * @see http://www.tutorialconnect.com/google-pagerank-checker-php-script/
  * @author Damian Kęska
  * @author tutorialconnect.com
  */

class GooglePR
{
    /**
     * Go sleep to make a pause between HTTP requests
     * 
     * @return int
     */
    
    public static function goSleep($exec=True, $max=10)
    {
        $s = rand(floor($max/2), $max);
        if ($exec) sleep($s);
        return $s;
    }
    
    /**
     * Convert string to a number
     * 
     * @return int
     */
    
	public static function stringToNumber($string, $check, $magic)
	{
		$int32 = 4294967296;  // 2^32
		$length = strlen($string);
		for ($i = 0; $i < $length; $i++) {
			$check *= $magic;
			// If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
			// the result of converting to integer is undefined
			// refer to http://www.php.net/manual/en/language.types.integer.php
			if($check >= $int32) {
				$check = ($check - $int32 * (int) ($check / $int32));
				// if the check less than -2^31
				$check = ($check < -($int32 / 2)) ? ($check + $int32) : $check;
			}
			$check += ord($string{$i});
		}
		return $check;
	}
    
    /**
     * Create a url hash
     * 
     * @author tutorialconnect.com
     * @return string
     */

	public static function createHash($string)
	{
		$check1 = self::stringToNumber($string, 0x1505, 0x21);
		$check2 = self::stringToNumber($string, 0, 0x1003F);

		$factor = 4;
		$halfFactor = $factor/2;

		$check1 >>= $halfFactor;
		$check1 = (($check1 >> $factor) & 0x3FFFFC0 ) | ($check1 & 0x3F);
		$check1 = (($check1 >> $factor) & 0x3FFC00 ) | ($check1 & 0x3FF);
		$check1 = (($check1 >> $factor) & 0x3C000 ) | ($check1 & 0x3FFF);

		$calc1 = (((($check1 & 0x3C0) << $factor) | ($check1 & 0x3C)) << $halfFactor ) | ($check2 & 0xF0F );
		$calc2 = (((($check1 & 0xFFFFC000) << $factor) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000);

		return ($calc1 | $calc2);
	}
    
    /**
     * Create checksum for hash
     * 
     * @author tutorialconnect.com
     * @return string
     */

	public static function checkHash($hashNumber)
	{
		$check = 0;
		$flag = 0;

		$hashString = sprintf('%u', $hashNumber);
		$length = strlen($hashString);

		for ($i = $length - 1;  $i >= 0;  $i --) {
			$r = $hashString{$i};
			if(1 === ($flag % 2)) {
				$r += $r;
				$r = (int)($r / 10) + ($r % 10);
			}
			$check += $r;
			$flag ++;
		}

		$check %= 10;
		if(0 !== $check) {
			$check = 10 - $check;
			if(1 === ($flag % 2) ) {
				if(1 === ($check % 2)) {
					$check += 9;
				}
				$check >>= 1;
			}
		}
		return '7'.$check.$hashString;
	}
    
    /**
     * Get Google Pagerank value
     * 
     * @param string $page Domain
     * @param httplib $httplib Optional httplib object to use (can be used to preconfigure proxy server or save session cookies)
     * @author Damian Kęska
     * @return int -1 on failure, 0-10 on success
     */

	public static function getRank($page, $httplib=null)
	{
	    if (!$httplib)
            $httplib = new httplib;
        
        httplib::$userAgent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008072403 Mandriva/3.0.1-1mdv2008.1 (2008.1) Firefox/3.0.1";
        $result = $httplib -> get('http://toolbarqueries.google.com/tbr?client=navclient-auto&ch='.self::checkHash(self::createHash($page)).'&features=Rank&q=info:'.$page.'&num=100&filter=0');
        
        if ($result)
        {
            $pos = strpos($result, "Rank_");
            
            if($pos !== false)
                return intval(substr($result, $pos + 9));
        }
		
		return -1;
	}
    
    /**
     * Google Web Search using standard desktop search https://www.google.com page (no any API)
     * 
     * @param string $query Query string eg. site:pantheraframework.org or "anarchism"
     * @param int $page Page, default is 1
     * @param string|array Optional GET params eg. "hl=pl" or "http://..../search?hl=pl&testparam=1" or array('hl' => 'pl')
     * @param string $lang Optional lang (hl param) eg. pl
     * @param httplib Optional httplib object (to keep session cookies etc.)
     * @author Damian Kęska
     * @return array Returns array of all query params, results, pager navigation links
     */
    
    public static function webSearch($query, $page=1, $params=null, $lang='', $httplib=null)
    {
        include_once PANTHERA_DIR. '/share/phpQuery.php';
        
        if (!$httplib)
            $httplib = new httplib;
        
        $panthera = pantheraCore::getInstance();
        
        // GET parameters
        $args = array();
        
        if ($params)
        {
            if (!is_array($params))
            {
                $parsedURL = parse_url($params, PHP_URL_QUERY);
                
                if ($parsedURL)
                    $params = $parsedURL;
                
                parse_str($params, $params);
            }
            
            if (isset($params['q'])) unset($params['q']);
            if (isset($params['safe'])) unset($params['safe']);
            if (isset($params['start'])) unset($params['start']);
            
            if (is_array($params) and $params)
                $args = array_merge($args, $params);
        }
        
        if ($page and $page >= 2)
            $args['start'] = (($page * 10) - 10);
        else
            $args['start'] = 0;

        $args['q'] = $query;
        $args['safe'] = 'off';
        
        if ($lang)
            $args['hl'] = $lang;
        
        $url = 'https://www.google.com/search?' .http_build_query($args);
        
        $resultSet = array(
            'navigationPagesLinks' => array(),
            'results' => array(),
            'resultsLimitPos' => 0,
            'resultsLimitTo' => 0,
            'searchEndsAt' => false,
            'url' => $url,
            'page' => $page,
            'pages' => 0,
            'pagePosStart' => $args['start'],
            'pagePosEnd' => ($args['start']+10),
            'args' => $args,
        );
        
        $result = $httplib -> get($url);
        
        if (strpos($result, '<img src="/sorry/image?id=') !== False)
            throw new Exception('Google captcha detected, exiting');
        
        /*$fp = fopen('/tmp/googlesearch', 'w');
        fwrite($fp, $result);
        fclose($fp);
        $result = file_get_contents('/tmp/googlesearch');*/
        $pq = phpQuery::newDocument($result);
        
        // remove ads-block
        $pq['#rhs_block'] -> remove();
        
        /** NAVIGATION LINKS **/
        
        $resultSet['navigationPagesLinks'][1] = $url;
        $elements = $pq['.fl'];
        
        if ($elements)
        {
            foreach ($elements as $element)
            {
                $element = pq($element);
    
                $pageNum = intval(trim(strip_tags($element -> html())));
                          
                // if it's not a page number
                if (!$pageNum)
                    continue;
                
                $href = $element -> attr('href');
                
                if ($href)
                {
                    if (strpos($href, "/search") !== 0)
                        continue;
                    
                    $resultSet['navigationPagesLinks'][$pageNum] = 'https://www.google.com' .$href;
                }
            }

            // position that includes all positions
            reset($resultSet['navigationPagesLinks']);
            $firstPage = key($resultSet['navigationPagesLinks']);
            end($resultSet['navigationPagesLinks']);
            $lastPage = key($resultSet['navigationPagesLinks']);
            
            if ($firstPage == 1)
                $resultSet['resultsLimitPos'] = 0;
            else {
                $resultSet['resultsLimitPos'] = (($firstPage * 10) - 10);
            }
            
            $resultSet['resultsLimitTo'] = ($lastPage * 10);
            
            // by default Google's pager shows up to 10 pages. If there is less than 10 links showed we can conclude it's end of search
            if (count($resultSet['navigationPagesLinks']) < 10)
                $resultSet['searchEndsAt'] = count($resultSet['navigationPagesLinks']);
            
        } else {
            $panthera -> logging -> output('No results found', 'GooglePR');
        }
        
        
        $elements = $pq['.ads-ad, .rc'];
        
        foreach ($elements as $element)
        {
            $element = pq($element);
            $tmp = phpQuery::newDocument($element['h3'] -> html());
            $link = '';
            $map = False;
            
            if (strpos($element -> html(), '<div class="_qx">') !== False)
                $map = True;
            
            foreach ($tmp['a'] as $a)
            {
                $a = pq($a);
                $link = $a -> attr('href');
                break;
            }
            
            $type = 'link';
            
            if ($element['.ads-badge'] -> html())
                $type = 'ad';
            
            $resultSet['results'][] = array(
                'title' => strip_tags($element['h3'] -> html()),
                'link' => $link,
                'type' => $type,
                'map' => $map,
            );
            
        }
        
        return $resultSet;
    }

    /**
     * Get count of indexed domain pages
     * 
     * @param string $domain Domain name eg. afed.org.uk
     * @param httplib $httplib Object of httplib (to keep the session with all settings, proxy up)
     * @param bool $useGoogleApis (Optional) Allow to use ajax.googleapis.com (this may be unstable but worth testing)
     * @author Damian Kęska
     * @return int
     */

    public static function getSiteRank($domain, $httplib=null, $useGoogleApis=False, $sleep=0)
    {
        $panthera = pantheraCore::getInstance();
        
        if (!$httplib)
            $httplib = new httplib;
        
        $panthera -> logging -> output('Getting site rank for "' .$domain. '" domain', 'GooglePR');
        
        if ($useGoogleApis)
        {
            /** Try to use ajax.googleapis.com if available **/
            try {
                $panthera -> logging -> output('Trying to use ajax.googleapis.com', 'GooglePR');
                
                $response = json_decode($httplib -> get('http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=site:' .$domain. '&filter=0'), true);
                
                if ($response['responseStatus'] == 200)
                    return intval($response['responseData']['cursor']['estimatedResultCount']);
                else
                    $panthera -> logging -> output('API returned bad code: ' .$response['responseData']['responseStatus'], 'GooglePR');
                
            } catch (Exception $e) { /* pass */ }
        }
        
        /** If not try desktop version **/
        $results = static::webSearch('site:' .$domain, null, null, null, $httplib);
        
        while (!$results['searchEndsAt'])
        {
            if ($sleep)
                $this -> goSleep(True, $sleep);
            
            end($results['navigationPagesLinks']);
            $panthera -> logging -> output('Counting page "' .key($results['navigationPagesLinks']). '"', 'GooglePR');
            $results = static::webSearch('site:' .$domain, key($results['navigationPagesLinks']), /*$results['navigationPagesLinks'][key($results['navigationPagesLinks'])]*/null, '', $httplib);
        }
        
        $httplib -> close();
        return ($results['searchEndsAt']*10);
    }
    
    /**
     * Get domain position for selected keyword
     * 
     * @param string $keyword Search query
     * @param string $domain Domain name
     * @param string $lang Search language (eg. pl, default: none)
     * @param string $excludeAds Exclude ads from results
     * @param string $excludeMaps Exclude maps from results
     * @param int $maxPos Max position to check, if not reached return false
     * @param httplib $httplib httplib object
     * @return int|bool Returns false when no any result found, and int with position on positive result
     */

    public static function getKeywordPosition($keyword, $domain, $lang='', $excludeAds=False, $excludeMaps=False, $httplib=null, $sleep=0, $maxPos=100)
    {
        if (!$httplib)
            $httplib = new httplib;
        
        $panthera = pantheraCore::getInstance();
        $panthera -> logging -> output('Looking up "' .$keyword. '" position for "' .$domain. '"', 'GooglePR');
        
        $maxPages = 100;
        $found = False;
        $position = false;
        $page = 0;
        $pos = 0;
        
        while (True)
        {
            // sleep to try to avoid ban
            if ($sleep)
                static::goSleep(True, $sleep);
            
            $page++;
            
            try {
                $search = static::webSearch($keyword, $page, null, $lang, $httplib);
            } catch (Exception $e) {
                $pos += 10; // skip page and add 10 positions
            }
            
            if (!count($search['results']))
                break;
            
            foreach ($search['results'] as $result)
            {
                if ($excludeAds and $result['type'] == 'ad')
                    continue;
                
                if ($excludeMaps and $result['map'])
                    continue;
                
                $pos++;

                // if found, return position                
                if (strpos(parse_url($result['link'], PHP_URL_HOST), $domain) !== False)
                {
                    $panthera -> logging -> output('Found keyword position at ' .$pos, 'GooglePR');
                    return $pos;
                }
            }
            
            // sprawdzamy tylko do 10 pozycji
            if ($page >= ($maxPos/10))
            {
                $panthera -> logging -> output('Max position "' .$maxPos. '" (' .$page. ' reached), keyword position not found', 'GooglePR');
                break;
            }
        }
        
        return False;
    }
}
