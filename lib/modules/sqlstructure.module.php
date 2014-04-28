<?php
define('CURRENT_TIMESTAMP', 8);

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

				$results[$tableName] = array(
					'columns' => array(),
					'indexes' => array(),
					'engine' => null,
					'charset' => '',
				);

                continue;
            }
            
            $line = trim($line);
            $firstChar = substr($line, 0, 1);
            
            if ($firstChar == '`' or $firstChar == '"')
                $this -> parseColumn($line, $tableName, $results);

			// MySQL like syntax: PRIMARY KEY / UNIQUE KEY
			if (substr($line, 0, 11) == 'PRIMARY KEY' or substr($line, 0, 10) == 'UNIQUE KEY' or substr($line, 0, 11) == 'FOREIGN KEY')
			{
				preg_match_all('/\(([A-Za-z0-9_\.\,"\'`]+)\)/', $line, $_matches, PREG_SET_ORDER);

				if ($_matches)
				{
				    $column = str_replace(array('`', '"', '\''), '', $_matches[0][1]); // unquote string
				
				    if (strpos($line, 'PRIMARY KEY') !== False)
				    {
				        $keyType = 'PRIMARY KEY';
				        $results[$tableName]['columns'][$column]['primaryKey'] = true;
				        
				    } elseif (strpos($line, 'UNIQUE KEY') !== False) {
				        $keyType = 'UNIQUE KEY';
				        $results[$tableName]['columns'][$column]['uniqueKey'] = true;
				        
				    } elseif (strpos($line, 'FOREIGN KEY') !== False) {
				        $keyType = 'FOREIGN KEY';
				        $results[$tableName]['columns'][$column]['foreignKey'] = true;
				    }
					
					$results[$tableName]['indexes'][$column] = array(
                        'type' => $keyType,
                        'length' => null,
                    );
				}
			}
			
			// MySQL stores extra informations and final line of every CREATE TABLE statement
			if (substr($line, 0, 1) == ')')
			{
			    preg_match_all('/([A-Za-z0-9_ ]+)\=([A-Za-z0-9_\-\+]+)/', $line, $_matches, PREG_SET_ORDER);
			    
			    $attrs = array();
			    
			    foreach ($_matches as $match)
			    {
			        $key = strtoupper(trim($match[1]));
			        $value = trim($match[2]);
			        
			        if (is_numeric($value))
			            $value = intval($value);
			        
			        $attrs[$key] = $value;
			    }
			    
			    $results[$tableName]['__mysqlRawAttrs'] = $attrs;
			    
			    if (isset($attrs['ENGINE']))
			        $results[$tableName]['engine'] = $attrs['ENGINE'];
			        
			    if (isset($attrs['DEFAULT CHARSET']))
			        $results[$tableName]['charset'] = $attrs['DEFAULT CHARSET'];
			}
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
			'onUpdate' => null,
        );
        
        preg_match_all('/DEFAULT ([\'"])?(.+)?([\'"]+)([,]+)?/', $line, $_matches, PREG_SET_ORDER);
        
		$line = preg_replace('/"(.+)"/', '', $line);
        $line = preg_replace("/'(.+)'/", '', $line);

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

		if (strpos($line, ' DEFAULT CURRENT_TIMESTAMP') !== False)
			$data['default'] = CURRENT_TIMESTAMP;

		if (strpos($line, 'ON UPDATE CURRENT_TIMESTAMP') !== False)
			$data['onUpdate'] = CURRENT_TIMESTAMP;

        // data is passed by reference
        $results[$tableName]['columns'][$data['name']] = $data;
    }
    
    /**
     * Parse CREATE INDEX statements
     *
     * @param string $line Input line
     * @param array &$results
     *
     * @return null
     */

    protected function parseIndex($line, &$results)
    {
        // for MySQL:
        // CREATE INDEX `id` ON test (id(123));
        // CREATE INDEX `id` ON test (id);
        
        // for SQLite3:
        // CREATE INDEX "{$db_prefix}config_overlay_key" ON "{$db_prefix}config_overlay" ("key");
        
        preg_match_all('/CREATE INDEX?(\d*)([`\"])?(.+)?([`\"])?(\d*)ON?(\d*)([`\" ])?(.+)?([`\" ])(\d*)\((.+)\)/', $line, $_matches, PREG_SET_ORDER);
        
        // 3 => index name
        // 8 => table name
        // 11 => column
        
        if ($_matches)
        {
            $index = $this->stripQuotes($_matches[0][3], true);
            $table = $this->stripQuotes($_matches[0][8]);
            $column = $this->stripQuotes($_matches[0][11]);
            $columnLength = null;
            $tableFound = False;
            
            foreach ($results as $tableName => $attrs)
            {
                // check if index is created on existing table
                if ($tableName == $table)
                    $tableFound = True;
                    
                // SQLite3-like keys naming
                if (strpos($index, $tableName) !== False)
                {
                    // this will remove table from key name eg. "{$db_prefix}config_overlay_id" => "id"
                    $index = str_replace($tableName. '_', '', $index);
                }
            }
            
            if (!$tableFound)
                throw new Exception('Tried to create index on non-existent table "' .$table. '"');
                
            // MySQL syntax also supports length attribute
            if (strpos($column, '(') !== False)
            {
                preg_match_all('/([A-Za-z0-9_]+)\(([0-9]+)\)/', $column, $_matches, PREG_SET_ORDER);
                
                // 1 => column
                // 2 => length
                
                if ($_matches)
                {
                    $column = $_matches[0][1];
                    $columnLength = $_matches[0][2];
                }
            }
            
            if (!isset($results[$table]['columns'][$column]))
            {
                throw new Exception('Tried to add index to non-existent column "' .$column. '" in "' .$table. '" table');
            }
            
            // update index list
            $results[$table]['indexes'][$column] = array(
                'type' => 'PRIMARY KEY',
                'length' => $columnLength,
            );
            
            // update columns list
            $results[$table]['columns'][$column]['primaryKey'] = true;
        }
    }
    
    /**
     * Strip quotes from string
     * 
     * @param string $input Input string
     * @param bool $brackets Also strip brackets too
     * @return string
     */
    
    public function stripQuotes($input, $brackets=False)
    {
        $quotes = array(
            '"', "'", '`',
        );
        
        if ($brackets)
        {
            $quotes[] = '(';
            $quotes[] = ')';
        }
        
        return trim(str_replace($quotes, '', trim($input)));
    }
}
