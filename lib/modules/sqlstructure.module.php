<?php
class SQLStructure
{
    protected $raw = array();
    
    /**
     * Constructor
     * 
     * @return null
     */
    
    public function __construct($createTableString='')
    {
        if ($createTableString)
            $this -> load($createTableString);
    }
    
    /**
     * Load input CREATE TABLE string
     * 
     * @param string $createTableString Input statement string
     * @return null
     */
    
    public function load($createTableString)
    {
        $this -> raw = explode("\n", $createTableString);
    }
    
    /**
     * Parse data and return in array
     * 
     * @return array
     */
    
    public function getParsedArray()
    {
        $tableName = null;
        $results = array();
        
        // Table definitions (CREATE TABLE syntax)
        foreach ($this -> raw as $line)
        {
            if (strpos($line, 'CREATE TABLE') !== False)
            {
                // clean up the string
                $line = str_ireplace(array(
                    'CREATE TABLE ',
                    '"',
                    '`',
                    '(',
                    'IF NOT EXISTS',
                ), '', $line);
                
                $line = trim($line);
                $tableName = $line;
                continue;
            }
            
            $line = trim($line);
            $firstChar = substr($line, 0, 1);
            
            if ($firstChar == '`' or $firstChar == '"')
                $this -> parseColumn($line, $tableName, $results);
        }
        
        // Indexes (CREATE INDEX syntax)
        foreach ($this -> raw as $line)
        {
            $line = trim($line);
            
            if (substr($line, 0, 12) == "CREATE INDEX")
                $this -> parseIndex($line, $results);
        }
        
        return $results;
    }
    
    /**
     * Parse line containing column definition and add to &$results
     * 
     * @param string $line Input line to parse
     * @param string $tableName Table name
     * @param array &$results Results array
     * 
     * @return null
     */
    
    public function parseColumn($line, $tableName, &$results)
    {
        $inputData = $line;
        
        //$line = str_replace('`', '"', $line); // SQLite3 test
        preg_match_all("/([`\"])?([A-Za-z_\.]+)?([`\"]) (\w+)\(? ?(\d*) ?\)?/", $line, $_matches, PREG_SET_ORDER);
        
        if (!$_matches)
        {
            throw new Exception('Error parsing line "' .$line. '"');
        }
        
        $_matches = $_matches[0];
        
        $data = array(
            'name' => $_matches[2],
            'type' => strtolower($_matches[4]),
            'length' => intval($_matches[5]),
            'default' => null,
            'null' => true,
            'autoIncrement' => false,
            'primaryKey' => false,
            'uniqueKey' => false,
            'foreignKey' => false,
            '__inputData' => $inputData,
        );
        
        preg_match_all('/DEFAULT ([\'"])?(.+)?([\'"]+)([,]+)?/', $line, $_matches, PREG_SET_ORDER);
        
        // supported only in MySQL
        if ($_matches)
            $data['default'] = $_matches[0][2];
        
        if (strpos($line, ' NOT NULL') !== False)
            $data['null'] = false;

        /* SQLite specific syntax */        
        if (strpos($line, ' AUTO_INCREMENT') !== False or strpos($line, 'AUTOINCREMENT') !== False)
            $data['autoIncrement'] = true;
        
        if (strpos($line, ' PRIMARY KEY') !== False)
            $data['primaryKey'] = true;
        
        if (strpos($line, ' UNIQUE') !== False)
            $data['uniqueKey'] = true;
        
        if (strpos($line, ' FOREIGN KEY') !== False)
            $data['foreignKey'] = true;
        
        $line = preg_replace('/"(.+)"/', '', $line);
        $line = preg_replace("/'(.+)'/", '', $line);
        
        // data is passed by reference
        $results[$tableName]['columns'][$data['name']] = $data;
    }

    protected function parseIndex($line, &$results)
    {
        // for MySQL:
        // CREATE INDEX `id` ON test (id(123));
        // CREATE INDEX `id` ON test (id);
        
        // for SQLite3:
        // CREATE INDEX "{$db_prefix}config_overlay_key" ON "{$db_prefix}config_overlay" ("key");
        
        preg_match_all('/CREATE INDEX?(\d*)([`\"])?(.+)?([`\"])?(\d*)ON?(\d*)([`\"])?(.+)?([`\"])/', $line, $_matches, PREG_SET_ORDER);
        
        var_dump($line);
        var_dump($_matches);
        exit;
    }
}

$structure = new SQLStructure(file_get_contents('../database/templates/config_overlay.sql'));
$structure = new SQLStructure(file_get_contents('../database/templates/sqlite3/config_overlay.sql'));
print_r($structure -> getParsedArray());
