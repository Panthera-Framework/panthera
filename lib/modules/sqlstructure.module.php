<?php
/**
 * SQL CREATE TABLE statements parser (supports SQLite3 and MySQL)
 *
 * @package Panthera\modules\sqlstructure
 * @author Damian Kęska
 * @license GNU LGPLv3
 */

define('CURRENT_TIMESTAMP', 8);

/**
 * SQL CREATE TABLE statements parser (supports SQLite3 and MySQL)
 *
 * @package Panthera\modules\sqlstructure
 * @author Damian Kęska
 */

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

                if (!$_matches)
                    continue;
                
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

                $results[$tableName]['__dbType'] = 'mysql';
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

        if (strpos($line, ' AUTO_INCREMENT') !== False or strpos($line, 'AUTOINCREMENT') !== False)
            $data['autoIncrement'] = true;
        
        if (strpos($line, ' PRIMARY KEY') !== False)
            $data['primaryKey'] = true;
        
        if (strpos($line, ' UNIQUE') !== False)
        {
            $results[$tableName]['__dbType'] = 'mysql';
            $data['uniqueKey'] = true;
        }
        
        if (strpos($line, ' FOREIGN KEY') !== False)
        {
            $data['foreignKey'] = true;
            $results[$tableName]['__dbType'] = 'mysql';
        }
        
		if (strpos($line, ' DEFAULT CURRENT_TIMESTAMP') !== False)
        {
			$data['default'] = CURRENT_TIMESTAMP;
            $results[$tableName]['__dbType'] = 'mysql';
        }
        
		if (strpos($line, 'ON UPDATE CURRENT_TIMESTAMP') !== False)
        {
			$data['onUpdate'] = CURRENT_TIMESTAMP;
            $results[$tableName]['__dbType'] = 'mysql';
        }
        
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
    
    /**
     * Strip out MySQL functions into SQLite3 features
     * 
     * @param array $input
     * @return array
     */
    
    public function sqliteCompat($input)
    {
        $mysqlToSQLite3Types = array(
            'INTEGER' => array('INT', 'TINYINT', 'INTEGER', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'UNSIGNED BIG INT', 'INT2', 'INT8'),
            'TEXT' => array('CHARACTER', 'VARCHAR', 'VARYING CHARACTER', 'NCHAR', 'NATIVE CHARACTER', 'NVARCHAR', 'CLOB', 'TEXT'),
            'BLOB' => array('BLOB'),
            'REAL' => array('DOUBLE', 'DOUBLE PRECISION', 'FLOAT', 'REAL'),
            'NUMERIC' => array('DECIMAL', 'BOOLEAN', 'DATE', 'DATETIME', 'NUMERIC'),
        );
        
        foreach ($input['columns'] as &$column)
        {
            // SQLite does not support UNIQUE and FOREIGN KEYS
            $column['uniqueKey'] = False;
            $column['foreignKey'] = False;
            
            // there are no fixed lengths
            $column['length'] = 0;
            
            // no ON UPDATE triggers
            $column['onUpdate'] = null;
            
            $type = strtoupper($column['type']);
            $found = False;
            
            if (!in_array($type, $mysqlToSQLite3Types))
            {
                foreach ($mysqlToSQLite3Types as $typeName => $t)
                {
                    if (in_array($type, $t))
                    {
                        $found = True;
                        $type = $column['type'] = $typeName;
                        break;
                    }
                }
            }
            
            // debugging informations
            unset($column['__inputData']);
        }
        
        // MySQL attributes
        $input['engine'] = null;
        $input['charset'] = '';
        unset($input['__mysqlRawAttrs']);
        unset($input['__dbType']);
        
        return $input;
    }
    
    /**
     * Create a diff between $a and $b
     * 
     * @param SQLStructure $a Object A
     * @param SQLStructure $b Object B
     * @author Damian Kęska
     * @return array
     */
    
    public function compareWith($b, $tableName)
    {
        $a = $this -> getParsedArray();
        $b = $b -> getParsedArray();
        
        $a = $a[$tableName];
        $b = $b[$tableName];
        
        // if we are comparing SQLite3 and MySQL
        if ($a['__dbType'] != $b['__dbType'])
        {
            $a = $this -> sqliteCompat($a);
            $b = $this -> sqliteCompat($b);
        }
        
        $i = 1;
        $diff = arrayRecursiveDiff($a, $b, $i);
        $i--;
        
        if (isset($diff['__mysqlRawAttrs']['AUTO_INCREMENT']))
        {
            unset($diff['__mysqlRawAttrs']['AUTO_INCREMENT']);
            unset($diff['__mysqlRawAttrs']['__meta_AUTO_INCREMENT']);
            $i--;
        }
        
        if (isset($diff['__mysqlRawAttrs']) and !$diff['__mysqlRawAttrs'])
            unset($diff['__mysqlRawAttrs']);
        
        return array(
            'a' => $a,
            'b' => $b,
            'diff' => $diff,
            'countDiffs' => $i,
            'tableName' => $tableName,
        );
    }
    
    /**
     * Generate a MySQL patch that can be applied on a live database to update structure
     * 
     * @param array $diff Result of compareWith() method
     * @return string
     */
    
    public static function __generateSQLPatchMySQL($diff)
    {
        if (!is_array($diff) or !isset($diff['diff']) or !is_array($diff['diff']))
            throw new Exception('First argument of generateSQLPatch() does not look like a compareWith() method result', 162);
        
        $patch = '';
        
        // ADDING, MODIFING and DROPING columns
        if ($diff['diff']['columns'])
        {
            foreach ($diff['diff']['columns'] as $column => $attr)
            {
                $attr = array_merge($attr, $diff['a']['columns'][$column], $diff['b']['columns'][$column]);
                
                if (substr($column, 0, 7) == '__meta_')
                    continue;
                
                $operation = "MODIFY";
                
                // if column was created
                if (isset($diff['diff']['columns']['__meta_'.$column]) and $diff['diff']['columns']['__meta_'.$column] == 'created')
                    $operation = "ADD";
                elseif (isset($diff['diff']['columns']['__meta_'.$column]) and $diff['diff']['columns']['__meta_'.$column] == 'removed')
                    $operation = "DROP";
                
                $patch .= "ALTER TABLE `" .$diff['tableName']. "` ".$operation." `".$column."`";
                
                // DROP operation
                if ($operation == "DROP")
                {
                    $patch .= ";\n";
                    continue;
                }
                   
                // MODIFY and ADD operations
                $patch .= " ".$attr['type'];
                
                if (intval($attr['length']))
                    $patch .= "(".$attr['length'].")";
                
                if (!$attr['null']) {$patch .= " NOT NULL"; } else { $patch .= " NULL";}
                if ($attr['default']) $patch .= " DEFAULT \"".$attr['default']."\"";
                if ($attr['autoIncrement']) $patch .= " AUTO_INCREMENT";
                if ($attr['primaryKey']) $patch .= " PRIMARY KEY";
                if ($attr['uniqueKey']) $patch .= " UNIQUE KEY";
                if ($attr['foreignKey']) $patch .= " FOREIGN KEY";
                if ($attr['onUpdate']) { if($attr['onUpdate'] === 8) { $attr['onUpdate'] = 'CURRENT_TIMESTAMP'; }  $patch .= " ON UPDATE ".$attr['onUpdate']; }
                $patch .= ";\n";
            }
        }

        // MySQL attributes
        if ($diff['diff']['__mysqlRawAttrs'])
        {
            foreach ($diff['diff']['__mysqlRawAttrs'] as $key => $value)
            {
                if (substr($key, 0, 7) == '__meta_')
                    continue;
                
                if ($key == 'DEFAULT CHARSET' or $key == 'CHARACTER SET')
                    $key = 'CHARACTER SET ';
                else {
                    $key .= ' = ';
                }
                
                $patch .= "ALTER TABLE `" .$diff['tableName']. "` ".$key.$value.";\n";
            }
        }
        
        return $patch;
    }

    /**
     * Generate a SQLite3 patch that can be applied on a live database to update structure
     * 
     * @param array $diff Result of compareWith() method
     * @return string
     */

    public function __generateSQLPatchSQLite3($diff)
    {
        return '';
    }
    
    /**
     * Generate a SQLite3 or MySQL patch that can be applied on a live database to update structure
     * 
     * @param array $diff Result of compareWith() method
     * @return string
     */
    
    public static function generateSQLPatch($diff, $dbType='sqlite')
    {
        if ($dbType == 'mysql')
            return static::__generateSQLPatchMySQL($diff);
        else
            return static::__generateSQLPatchSQLite3($diff);
    }
}
