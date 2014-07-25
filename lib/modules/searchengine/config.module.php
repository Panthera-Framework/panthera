<?php
class configSearchengine
{
    /**
     * Query database
     * 
     * @param string $query Query string
     * @param int $offset Results offset
     * @param int $limit Results limit
     * @return array|int
     */
    
    public function query($query, $offset=0, $limit=0)
    {
        $filter = new whereClause;
        $filter -> add('OR', 'key', 'LIKE', '%' .$query. '%');
        $filter -> add('OR', 'value', 'LIKE', '%' .$query. '%');
        $filter -> add('OR', 'section', 'LIKE', '%' .$query. '%');
        
        $this -> getFeatureRef('searchengine.config.filter', $filter, $this);
        
        // get results count
        if ($offset === False)
            return panthera::getInstance() -> db -> getRows('config_overlay', $filter, False, False);
        
        // collect results
        $temporaryData = panthera::getInstance() -> db -> getRows('config_overlay', $filter, $limit, $offset);
        $data = array();
        
        foreach ($temporaryData as $row)
        {
            $data[] = array(
                'source' => $row,
                'type' => 'config',
                'title' => 'Configuration key - ' .$row['key'],
                'description' => $row['value'],
                'image' => null,
                'link' => pantheraUrl('{$AJAX_URL}?display=conftool&cat=admin'),
                'tags' => array(
                    $row['key'], $row['value'], $row['section'],
                ),
            );
        }
        
        return $data;
    }
}
