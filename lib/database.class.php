<?php
/**
  * Database management classes and functions, helpers
  * @package Panthera\core\database
  * @author Damian Kęska
  */

/**
  * Panthera Database class
  *
  * @package Panthera\core\database
  * @author Damian Kęska
  */

class pantheraDB extends pantheraClass
{
    public $sql, $prefix, $sqlCount=0, $cache=120;
    protected $socketType;
    protected $fixMissing=False;
    protected $deepCount=0;
    protected $missing = array();
    protected $config;
    
    /**
      * Prepare database connection
      *
      * @param object $panthera
      * @config build_missing_tables
      * @return void 
      * @author Damian Kęska
      */

    public function __construct($panthera, $alternativeConfig='', $dontTriggerError=False)
    {
        parent::__construct();
        $config = $panthera->config->getConfig();

        if ($alternativeConfig != '')
            $config = $alternativeConfig;
            
        $this->cache = intval(@$config['cache_db']);
        
        // database timeout
        if (!isset($config['db_timeout']))
            $config['db_timeout'] = 5; // 5 seconds
        
        $config['db_timeout'] = intval(@$config['db_timeout']);
        $this->config = $config;        

        if ($this -> cache < 1)
            $this -> cache = 3600;
            
        // this setting will automaticaly import database structures from template if any does not exists
        if (@$config['build_missing_tables'] == True)
            $this->fixMissing = True;
            
        try {
            // selecting between SQLite3 and MySQL database
            if (strtolower(@$config['db_socket']) == 'sqlite')
            {
                if (!is_file(SITE_DIR. '/content/database/' .$config['db_file']))
                    throw new Exception('Database fils is missing in /content/database/, please check app.php (variable - db_file) and file name');

                $this->socketType = 'sqlite';
                $this->sql = new PDO('sqlite:' .SITE_DIR. '/content/database/' .$config['db_file']);
                $this->sql->setAttribute( PDO::ATTR_STATEMENT_CLASS, array('pantheraDBStatement',array($this->sql, $this)) );
                $this->sql->exec("pragma synchronous = off;");
                
                $panthera -> logging -> output('Connected to SQLite3 database file ' .$config['db_file'], 'pantheraDB');
            } else {
                $this->socketType = 'mysql';
                $this->sql = @new PDO('mysql:host='.$config['db_host'].';encoding=utf8;charset=utf8;dbname='.$config['db_name'], $config['db_username'], $config['db_password']);
                $panthera -> logging -> output('Connected to MySQL database, ' .$config['db_username']. '@' .$config['db_host'], 'pantheraDB');
                
                if (isset($config['db_mysql_buffered_queries']))
                    $this->sql->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, (bool)$config['db_mysql_buffered_queries']);
            }

            $this->sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->sql->setAttribute(PDO::ATTR_TIMEOUT, intval($config['db_timeout']));
            $this->sql->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if (isset($config['db_emulate_prepares']))
                $this->sql->setAttribute(PDO::ATTR_EMULATE_PREPARES, (bool)$config['db_emulate_prepares']);
                
            if (isset($config['db_autocommit']))
                $this->sql->setAttribute(PDO::ATTR_AUTOCOMMIT, (bool)$config['db_autocommit']);
                
            $this->prefix = $config['db_prefix'];


        } catch (Exception $e) {
            if ($dontTriggerError == False)
                $this->_triggerErrorPage($e);
            else
                throw new Exception($e->getMessage());
        }
    }
    
    /**
      * Get SQL socket type (eg. mysql, sqlite)
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function getSocketType()
    {
        return $this->socketType;
    }
    
    /**
      * Trigger SQL error page 
      *
      * @param Exception $e
      * @author Damian Kęska
      */

    public function _triggerErrorPage($e, $customWarningMsg='')
    {
        // a little bit hook to provide possibility to inform administrator about the error
        if (function_exists('userDBError'))
            userDBError($e);
            
        if ($customWarningMsg != '')
            $warningMessage = $customWarningMsg;
        else
            $warningMessage = 'Cannot connect to database, please check your connection and database configuration in /content/app.php file.<br>You can also check your SQL user and database priviledges, allowed hosts and if server is online. ';

        
        if (strpos($e->getMessage(), 'No such file or directory') !== false)
        {
            $warningMessage = 'Cannot connect to database. The driver reports that there is no such file or directory, this can mean that the server is not running or not accessible because of any networking problems.';
        }

        $message = $this->hideAuthInfo($e -> getMessage());
        $debugTemplate = getErrorPageFile('db_error');

        // if database error page exists
        if (is_file($debugTemplate))
        {
            global $panthera;
            include($debugTemplate);
            exit;        
        }

        // if not we will show simple error
        die('<h2>Server error</h2><br>Unrecoverable database error: ' .$message);
    }
    
    /**
      * Hide passwords in a string
      *
      * @param string $string
      * @return string 
      * @author Damian Kęska
      */

    public function hideAuthInfo($string)
    {
        $string = str_ireplace($this->panthera->config->getKey('db_host'), '****', $string);
        $string = str_ireplace($this->panthera->config->getKey('db_username'), '****', $string);
        $string = str_ireplace($this->panthera->config->getKey('db_name'), '****', $string);
        $string = str_ireplace($this->panthera->config->getKey('db_password'), '****', $string);
        return $string;
    }

    /**
      * Perform a SQL query with optional $values
      *
      * @config build_missing_tables
      * @param string $query to send
      * @param array $values to pass to query
      * @param bool $retry Is this a retry query?
      * @return object 
      * @author Damian Kęska
      */
    
    public function query($query, $values=NuLL, $retry=False)
    {
        $this->sqlCount++;
        $query = str_ireplace('{$db_prefix}', $this->prefix, $query);
        
        if ($this->socketType == "sqlite")
        {
            $query = $this->translateToSQLite($query);
        }
        
        $this->panthera->logging->startTimer();

        // try to import missing tables if enabled
        if ($this->fixMissing == True)
        {
            try {
                $sth = $this->sql->prepare($query);

                if($values != NuLL)
                {
                    $this->bindArrayValue($sth, $values);
                }
            
                if (!$sth -> execute())
                    return False;

            } catch (PDOException $e) {
                if ($this->socketType == 'sqlite')
                {
                    if (strpos($e->getMessage(), 'General error: 17') !== False and !$retry)
                    {
                        $sth = $this->query($query, $values, True);
                        
                    } else {
                        $sth = $this->_fixMissingSQLite($e, $query, $values);
                    }
                    
                } elseif ($this->socketType == 'mysql')
                    $sth = $this->_fixMissingMySQL($e, $query, $values);
            }
            
        } else {
            $sth = $this->sql->prepare($query);

            if($values != NuLL)
            {
                $this->bindArrayValue($sth, $values);
            }
                
            if (!$sth -> execute())
                return False;
        }
        
        if ($this->panthera->logging->debug == True)
        {
            $this->panthera->logging->output('query( ' .$query. ' , ' .json_encode($values). ' )', 'pantheraDB');
        }
        
        return $sth;
    }
    
    /**
      * Simply translate some MySQL names to SQLite3 equivalents
      *
      * @param string $query
      * @return string
      * @author Damian Kęska
      */
    
    public function translateToSQLite($query)
    {
        $query = rtrim($query);
        
        if ($query[strlen($query)-1] != ';')
            $query .= ';';
                
        // MySQL functions
        $query = str_ireplace('NOW()', 'date(\'now\')', $query);
        
        return $query;
    }
    
    /**
      * This function should count missing tables fix operations and stop script when loop detected
      *
      * @param string $e Database Exception
      * @return void 
      * @author Damian Kęska
      */
    
    public function countMissingTables($e)
    {
        if (!isset($this->missing[$e->getMessage()]))
            $this->missing[$e->getMessage()] = 0;
    
        $this->missing[$e->getMessage()]++;
            
        if ($this->missing[$e->getMessage()] > 1)
        {
            $this->_triggerErrorPage($e, 'SQL table not found, cannot import it automaticaly, please import it manually from a template placed in ' .PANTHERA_DIR. '/database/');
        }
    }
    
    /**
      * Directly execute SQL statement
      *
      * @param string $SQL query
      * @return object 
      * @author Damian Kęska
      */
    
    public function execute($SQL)
    {
        $SQL = str_ireplace('{$db_prefix}', $this->prefix, $SQL);
        return $this->sql->exec($SQL);
    }
    
    /**
      * MySQL missing tables import
      *
      * @param object $e
      * @param string $query
      * @param array $values
      * @return object 
      * @author Damian Kęska
      */
    
    protected function _fixMissingMySQL ($e, $query, $values)
    {
        if ($e -> getCode() == "42S02" and stristr($query, 'CREATE TABLE') === False and stristr($query, 'DROP TABLE') === False)
        {
            $this->panthera -> logging -> output('Called fixMissing MySQL tables recovery', 'pantheraDB');
            
            $this->countMissingTables($e);
        
            preg_match("/'.*?'/", $e->getMessage(), $matches);
            
            if (count($matches) == 0)
            {
                return False;
            }
            
            $dbName = str_ireplace("'", '', str_ireplace($this->panthera->config->getKey('db_name'). '.', '', $matches[0])); // get only table name
            $dbName = str_ireplace($this->prefix, '', $dbName); // remove prefix
            $file = getContentDir('/database/templates/' .$dbName. '.sql');
            
            // debugging
            $this->panthera->logging->output('Importing missing MySQL table "' .$dbName. '"', 'pantheraDB');
            
            if (is_file($file))
            {
                $SQL = str_ireplace('{$db_prefix}', $this->prefix, file_get_contents($file));

                try {
                    $this->sql->exec($SQL);
                    return $this->query($query, $values);
                } catch (Exception $e) {
                    $this->_triggerErrorPage($e, 'Cannot create table, check template placed in ' .$file);
                }
                
            } else
                throw new Exception($e->getMessage());
            
        } else
            throw new Exception($e->getMessage());
    }
    
    /**
      * SQLite missing tables import
      *
      * @param object $e
      * @param string $query
      * @param array $values
      * @return object 
      * @author Damian Kęska
      */
    
    protected function _fixMissingSQLite($e, $query, $values)
    {
        if (strpos($e->getMessage(), 'no such table') !== False and strpos($query, 'CREATE TABLE') === False and stristr($query, 'DROP TABLE') === False)
        {
            $this->panthera -> logging -> output('Called fixMissing SQLite3 tables recovery (' .$e->getMessage(). ')', 'pantheraDB');
            $this->countMissingTables($e);
            
            $dbName = explode('no such table: ', $e->getMessage());
            $dbName = str_ireplace($this->prefix, '', $dbName[1]);
            $file = getContentDir('/database/templates/sqlite3/' .$dbName. '.sql');
            
            // debugging
            $this->panthera->logging->output('Importing missing SQLite3 table "' .$dbName. '"', 'pantheraDB');
            
            if (is_file($file))
            {
                $SQL = str_ireplace('{$db_prefix}', $this->prefix, file_get_contents($file));
                
                try {
                    $this->sql->exec($SQL);
                    return $this->query($query, $values);
                } catch (Exception $e) {
                    $this->_triggerErrorPage($e, 'Cannot create table, check template placed in "' .$file. '"');
                }
                
            } else
                throw new Exception($e->getMessage());
                
        } else 
            throw new Exception($e->getMessage());
    }
    
    /**
      * Duplicate a row in a table
      *
      * @param string $table Table name
      * @param int $idField Table's id field
      * @param int $idValue ID value of a record
      * @param array $newValues Optional values override
      * @return int 
      * @author Damian Kęska
      */
    
    public function duplicateRow($table, $idField, $idValue, $newValues = '')
    {
        $result = $this->query('SELECT * FROM `{$db_prefix}' .$table. '` WHERE `' .$idField. '` = :id', array('id' => $idValue));
        $array = $result->fetch();
        
        if (is_array($newValues))
            $array = array_merge($array, $newValues);
        
        unset($array[$idField]);

        $list = '';
        $values = '';
        $valuesArray = array();
        
        foreach ($array as $key => $value)
        {
            if (is_numeric($key))
                continue;
                
            $list .= '`' .$key. '`, ';
            $values .= ':' .$key. ', ';
            $valuesArray[$key] = $value;
        }
        
        $query = $this->query('INSERT INTO `{$db_prefix}' .$table. '` (`' .$idField. '`, ' .trim($list, ' ,'). ') VALUES (NULL, ' .trim($values, ' ,'). ')', $valuesArray);
        $newID = $this->sql->lastInsertId();
        return $newID;
        
    }
    
    /**
      * List tables in current database
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function listTables()
    {
        $tables = array();
        
        if ($this->socketType == 'sqlite')
        {
            $SQL = $this -> query ('SELECT * FROM sqlite_master WHERE type=\'table\';');
            
            foreach ($SQL -> fetchAll(PDO::FETCH_ASSOC) as $table)
            {
                if ($table['name'] == 'sqlite_sequence')
                    continue;
            
                $tables[] = $table['name'];
            }
        } else {
            $SQL = $this -> query ('SHOW TABLES FROM `' .$this->config['db_name'].'`');
            
            foreach ($SQL -> fetchAll(PDO::FETCH_ASSOC) as $table)
            {
                $tables[] = end($table);
            }
        }
        
        return $tables;
    }
    
    /**
      * Generate list of fields for SQL "UPDATE" query
      *
      * @param string name
      * @return array with first element containing eg. "`first` = :first, `second` = :second" and second element with array of values eg. array('first' => 'aaa', 'second' => 'bbb')  
      * @author Damian Kęska
      */

    function dbSet($fields, $sep=', ') 
    {
        $set = '';
        $values = array();

        foreach ($fields as $field => $value) 
        {
            if(is_numeric($field))
                continue;

            if (strtolower($value) == "now()")
            {
                $set .= "`".$field."` = NOW()".$sep;        
            } else {
                $values[$field] = $value;
                $set .= "`".$field."` = :".$field.$sep;
            }
        }
        
        return array(substr($set, 0, strlen($set)-strlen($sep)), $values);
    }
    
    /**
      * Build a simple "UPDATE" string
      *
      * @param array $array
      * @param array|string $ignoreColumns
      * @return array with query and values
      * @author Damian Kęska
      */
    
    public function buildUpdateString($array, $ignoreColumns=null)
    {
        if (!is_array($array))
        {
            return False;
        }
        
        if (!is_array($ignoreColumns))
        {
            $ignoreColumns = array();
        }
        
        if (is_string($ignoreColumns))
        {
            $ignoreColumns = trim(str_replace(' ', '', $ignoreColumns), ', ');
            $ignoreColumns = explode(',', $ignoreColumns);
        }
        
        $updateString = '';
        
        foreach ($array as $key => $value)
        {
            if (in_array($key, $ignoreColumns))
            {
                continue;
            }
        
            $updateString .= '`' .$key. '` = :' .$key. ', ';
        }
        
        return array('query' => trim($updateString, ', '), 'values' => $array);
    }
    
    /**
     * Build a SQL insert query string with single or multiple rows
     *
     * @param array $array Input array containing keys and values eg. array('id' => 1, 'title' => 'Test'), and for multiple rows: array(array('id' => 1, 'title' => 'First'), array('id' => 2, 'title' => 'Second'))
     * @param bool $multipleRows If $array contains multiple rows please set it to true
     * @param string $tableName Table name
     * @return array with query and values
     * @author Damian Kęska
     */
    
    public function buildInsertString($array, $multipleRows=False, $tableName='')
    {
        $columns = '';
        $queryTable = '';
        
        if ($tableName)
        {
            $queryTable = 'INSERT INTO `{$db_prefix}' .$tableName. '` ';
        }
        
        // single row code
        if (!$multipleRows)
        {
            $dataRow = '';
           
            foreach ($array as $key => $value)
            {
                $columns .= '`' .$key. '`, ';
                
                if ($value === '{$NOW()}')
                {
                    $dataRow .= 'NOW(), ';
                    unset($array[$key]);
                } else {
                    $dataRow .= ':' .$key. ', ';
                }
            }
            
            $columns = rtrim($columns, ', ');
            $dataRow = rtrim($dataRow, ', ');
            
            
            return array(
                'query' => $queryTable. '(' .rtrim($columns, ', '). ') VALUES (' .$dataRow. ')', 
                'values' => $array
            );
            
        } else {
            // multiple rows code
            
            foreach ($array[0] as $key => $value)
            {
                $columns .= '`' .$key. '`, ';
            }
            
            $dataRows = '';
            $i = 0;
            $values = array();
            
            foreach ($array as $row)
            {
                $i++;
                $dataRow = '(';
                
                foreach ($row as $key => $value)
                {
                    if ($value === '{$NOW()}')
                    {
                        $dataRow .= '{$NOW()}';
                    } else {
                        $dataRow .= ':' .$key. '_r' .$i. ', ';
                        $values[$key. '_r' .$i] = $value;
                    }
                }
                
                $dataRow = rtrim($dataRow, ', ');
                $dataRow .= ')';
                
                $dataRows .= $dataRow. ', ';
            }
            
            $dataRows = rtrim($dataRows, ', ');
        
            return array(
                'query' => $queryTable. '(' .rtrim($columns, ', '). ') VALUES ' .$dataRows,
                'values' => $values
            );
        }
    }

    /**
     * Make an INSERT query
     *
     * @param string $table Table name
     * @param array $array Input array containing keys and values eg. array('id' => 1, 'title' => 'Test'), and for multiple rows: array(array('id' => 1, 'title' => 'First'), array('id' => 2, 'title' => 'Second'))
     * @param bool $multipleRows If $array contains multiple rows please set it to true
     * @return PDOStatement
     * @author Damian Kęska
     */

    public function insert($table, $array, $multipleRows=False)
    {
        $str = $this -> buildInsertString($array, $multipleRows, $table);
        return $this -> query ($str['query'], $str['values']);
    }
    
    /**
     * Make a UPDATE query
     * 
     * @param string $table Table to query on
     * @param array $setArray List of columns an it's values eg. array('id' => 1, 'name' => 'Anne')
     * @param whereClause|string $whereClause Optional whereClause object (see whereClause class) or just a string like "`id` = 1 AND `name` = 'Anne'"
     * @param array|string List of columns to ignore eg. timestamps - array('date', 'id') or "date, id"
     * @return PDOStatement
     */
    
    public function update($table, $setArray, $whereClause=null, $ignoreColumns=null)
    {
        $setString = $this->buildUpdateString($setArray, $ignoreColumns);
        $query = 'UPDATE `{$db_prefix}' .$table. '` SET ' .$setString['query'];
        
        $vars = $setString['values'];
        
        if (!$whereClause and $whereClause !== null)
        {
            throw new Exception('$whereClause is empty but not a null value, please make sure you don\'t want to delete entire data from table', 7842);
        }
        
        if (is_object($whereClause))
        {
            if (!method_exists($whereClause, 'show'))
            {
                throw new Exception('$whereClause variable does not contain a valid object with show() method', 587);
            }
            
            $show = $whereClause->show();
            $query .= ' WHERE ' .$show[0];
            $vars = array_merge($vars, $show[1]);
        } elseif (is_string($whereClause)) {
            $query .= ' WHERE ' .$whereClause;   
        }
        
        return $this -> query($query, $vars);
    }
    
    /**
     * Delete query
     * 
     * @param string $table Table name to operate on
     * @param whereClause|string $whereClause whereClause class object or just a string
     * @param string $orderBy Optional column name to order by
     * @param string $orderDirection ASC or DESC (use with $orderBy)
     * @param int $limit Limit rows deletion
     */
    
    public function delete($table, $whereClause=null, $orderBy=null, $orderDirection='ASC', $limit=null)
    {
        $query = 'DELETE FROM `{$db_prefix}' .$table. '`';
        $vars = array();
        
        // take care about mistakes when $whereClause == "" but is not a null value
        if (!$whereClause and $whereClause !== null)
        {
            throw new Exception('$whereClause is empty but not a null value, please make sure you don\'t want to delete entire data from table', 7842);
        }
        
        if (is_string($whereClause))
        {
            $query .= ' WHERE ' .$whereClause;
            
        } elseif (is_object($whereClause)) {
            
            if (!method_exists($whereClause, 'show'))
            {
                throw new Exception('$whereClause variable does not contain a valid object with show() method', 587);
            }
            
            $show = $whereClause->show();
            $query .= ' WHERE ' .$show[0];
            $vars = array_merge($vars, $show[1]);
        }
        
        if (is_string($orderBy))
        {
            $query .= ' ORDER BY `' .$orderBy. '` ' .$orderDirection;
        }
        
        if (is_int($limit))
        {
            $query .= ' LIMIT ' .$limit;
        }
        
        return $this -> query($query, $vars);
    }
    
    /**
      * Get rows from selected database and return as array of data or array of specified class's objects
      *
      * @param string $db name
      * @param array $by columns and their values to match query results
      * @param int $limit
      * @param int $offset offset
      * @param int $returnAs leave empty to return array of data, put class name to return array of objects
      * @param string $orderColumn column to order by
      * @param string $order Order direction, default DESC
      * @return mixed 
      * @author Damian Kęska
      */

    public function getRows($db, $by, $limit, $offset, $returnAs='', $orderColumn='id', $order='DESC')
    {
        if (is_numeric($limit))
        {
            if (intval($limit) > 0)
            {
                $sqlLimit = ' LIMIT ' .intval($offset). ',' .intval($limit);
            }
        }
        
        $whereClause = '';
        $q = array('', '');

        if(is_array($by) or is_object($by))
        {
            if (!is_object($by))
            {
                $w = new whereClause();

                foreach ($by as $k => $v)
                {
                    if (strpos($k, '*LIKE*') !== False)
                    {
                        $w -> add( 'AND', str_replace('*LIKE*', '', $k), 'LIKE', $v);
                    } else {
                        $w -> add( 'AND', $k, '=', $v);
                    }
                }
            } else
                $w = $by;

            $q = $w -> show();
            
            if ($q[0])
                $whereClause = ' WHERE ' .$q[0];
        }

        $what = '*';

        if (is_bool($limit))
            $what = '`' .$orderColumn. '`';

        // get query from selected object
        if (is_object($returnAs))
        {
            if ($what == '*')
                $selectQuery = $returnAs->getQuery();
            else
                $selectQuery = $returnAs->getQuery('count');
            
            $returnAs = get_class($returnAs);
        } else {
            $selectQuery = 'SELECT ' .$what. ' FROM `{$db_prefix}' .$db. '`';
        }
        
        $SQL = $this->panthera->db->query($selectQuery.$whereClause. ' ORDER BY `' .$orderColumn. '` ' .$order.@$sqlLimit, @$q[1]);
        

        $results = array();

        if (is_bool($limit))
        {
            return $SQL->rowCount();
        }    

        if ($SQL->rowCount() > 0)
        {
        	// this code consumes less cpu and runs faster
            $array = $SQL->fetchAll(PDO::FETCH_ASSOC);

            foreach ($array as $item)
            {
                // return results as object
                if (class_exists($returnAs))
                    $results[] = new $returnAs('array', $item);
                else
                    $results[] = $item;
            }
			
			
			/*
			// this code consumes less memory but runs slower
			while ($item = $SQL -> fetch(PDO::FETCH_ASSOC))
			{
				// return results as object
				if (class_exists($returnAs))
                    $results[] = new $returnAs('array', $item);
                else
                    $results[] = $item;
			}*/
			
        }

        return $results;
    }
    
    /**
      * Create an unique value for database column
      *
      * @param string $table
      * @param string $column
      * @param string $title Optional title to parse and create an unique url
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function createUniqueData($table, $column, $title='')
    {
        if ($title)
        {
            $unique = $seoUrl = $title;
        } else {
            $unique = $seoUrl = generateRandomString(8);
        }
        
        $i = 0;
            
        do {
            $i++;
                
            if ($i > 1)
            {
                $unique = $seoUrl.$i;
            }
                
            if ($i > 10)
                $unique = hash('md4', rand(9999, 999999));
                
            $SQL = $this -> query('SELECT `' .$column. '` FROM `{$db_prefix}' .$table. '` WHERE `' .$column. '` = :unique', array('unique' => $unique));
        } while ( $SQL -> rowCount() > 0);
        
        return $unique;
    }
    
    /**
      * Bind array of values
      *
      * @param object $req
      * @param array $array
      * @param array|bool $typeArray Optional array specifing data types
      * @return mixed 
      * @author Damian Kęska
      */

    function bindArrayValue($req, $array, $typeArray = false)
    {//echo '<br><br>';
        if(is_object($req) && ($req instanceof PDOStatement))
        {
            foreach($array as $key => $value)
            {
                if($typeArray)
                {
                    //print('Bind '.$key.' = '.$value);
                    $req->bindValue(":$key",$value,$typeArray[$key]);
                } else {
                    $param = PDO::PARAM_STR;

                    if(is_int($value))
                        $param = PDO::PARAM_INT;
                    elseif(is_bool($value))
                        $param = PDO::PARAM_BOOL;
                    elseif(is_null($value))
                        $param = PDO::PARAM_NULL;
                    elseif(is_string($value))
                        $param = PDO::PARAM_STR;
                        
                    //print('Bind '.$key.' = '.$value. '<br>');
                    $req->bindValue(":$key",$value,$param);
                }
            }
        }
    }
}

/** Creating new WHERE clause for SQL query from array **/

//$w = new whereClause();
//$w -> add( 'AND', 'id', '=', 1);
//$w -> add( 'AND', 'data', '=', 'test');
//var_dump($w->show()); => array(2) { [0]=> string(27) " `id`=:id AND `data`=:data " [1]=> array(1) { ["data"]=> string(6) ""test"" } }

/**
  * Where clause generator
  *
  * @package Panthera\core\database
  * @author Damian Kęska
  */

class whereClause
{
	protected $SQL=NuLL, $vals = array(), $groups = array();
	
	/**
	  * Add statement before group of instructions
	  *
	  * @param int $group
	  * @param string $statement "AND" or "OR"
	  * @return void 
	  * @author Damian Kęska
	  */
	
	public function setGroupStatement($group, $statement)
	{
	    if ($statement != 'AND' and $statement != 'OR')
	    {
	        return False;
	    }
	    
	    if (!isset($this->groups[$group]))
	    {
	        $this->groups[$group] = array('query' => '', 'statement' => 'AND');
	    }
	    
	    $this->groups[$group]['statement'] = $statement;
	}
	
	/**
	  * Add new instruction
	  *
	  * @param string $Statement "OR", "AND", "", or ","
	  * @param string $Column
	  * @param string $Equals '=' , '!=', '<', '>', '<=', '>=', 'LIKE'
	  * @param mixed $Value
	  * @param int $group
	  * @return bool 
	  * @author Damian Kęska
	  */

	public function add ($Statement, $Column, $Equals, $Value, $group = 1)
	{
	    if (!isset($this->groups[$group]))
	    {
	        $this->groups[$group] = array('query' => '', 'statement' => 'AND');
	    }
	
        $this->Values = array();
		$Equals_list = array ( '=' , '!=', '<', '>', '<=', '>=', 'LIKE' );

		if ( !in_array ( $Equals, $Equals_list ) )
			return false;

		if ($Equals == 'LIKE')
			$Equals = ' LIKE '; // to be valid with syntax

		$Statement_list = array ( 'OR', 'AND', '', ',' );
	
		if ( !in_array ( $Statement, $Statement_list ) )
			return false;

		if (!$this->groups[$group]['query'])
		{
			$Statement = '';	
		}
		
		$columnTmp = $Column;
		
		while (isset($this->vals[$columnTmp]))
		{
		    $columnTmp = $Column.rand(0,9999);
		}
		
		$this->groups[$group]['query'] .= $Statement. ' `' .$Column. '` ' .$Equals. ' :' .$columnTmp. ' ';
        $this->vals[(string)$columnTmp] = $Value;
		return true;
	}
	
	/**
	  * Build and return query
	  *
	  * @return array with query and values 
	  * @author Damian Kęska
	  */

	public function show ()
	{
	    $this->SQL = '';
	
	    foreach ($this->groups as $group)
	    {
	        $this->SQL .= ' ' .$group['statement']. ' (' .$group['query']. ')';
	    }
	    
	    $this -> SQL = ltrim($this -> SQL, 'AND OR');
	
		return array($this->SQL, $this->vals);
	}
}

/**
  * Panthera Fetch DB - Turning database results into object, a data model with integrated caching and saving right back to database
  *
  * @package Panthera\core\database
  * @author Damian Kęska
  */

abstract class pantheraFetchDB
{
    protected $cacheGroup = 'pantheraFetchDB';
    protected $_dataModified = False; // save modifications to database?
    protected $_data = null; // cache of database row
    protected $_tableName = null; // table name
    protected $_idColumn = 'id';
    protected $_unsetColumns = array('created', 'modified', 'mod_time', 'last_result'); // columns we dont want to save
    protected $_constructBy = array('id', 'array');
    protected $panthera;
    protected $cache = 0;
    protected $cacheID = "";
    protected $treeID = '';
    protected $treeParent = '';
    protected $_joinColumns = array(
        /*array('LEFT JOIN', 'groups', array('group_id' => 'primary_group'), array('name' => 'group'))*/
    );
    protected $_queryCache = array(
        'joinColumns' => '',
        'joinQuery' => '',
    );
    
    /**
     * Get class inforamtions (used db table, columns)
     * 
     * return array
     */ 
    
    public function _getClassInfo()
    {
        return array(
            'idColumn' => $this->_idColumn,
            'unsetColumns' => $this->_unsetColumns,
            'constructBy' => $this->_constructBy,
            'joinColumns' => $this->_joinColumns,
            'tableName' => $this->_tableName,
            'treeID' => $this->treeID,
            'treeParent' => $this->treeParent,
        );
    }
    
    /**
     * Build a joined tables query
     * 
     * @param bool $selectString Build a list of columns for SELECT statement?
     * @param bool $force Skip in-class query cache (set to True if you modified $_joinColumns variable on the fly)
     * @return string
     */
    
    public function buildJoinQuery($selectString=False, $force=False)
    {
        $sql = '';
        
        if (!$this->_joinColumns)
            return "";
        
        
        // build list of columns for SELECT string eg. pa_groups.name as group_name
        if ($selectString)
        {
            // get this part of query string from cache
            if ($this->_queryCache['joinColumns'] and !$force)
                return $this->_queryCache['joinColumns'];
            
            foreach ($this->_joinColumns as $table)
            {
                foreach ($table[3] as $column => $alias)
                {
                    $sql .= '{$db_prefix}' .$table[1]. '.' .$column. ' as ' .$alias. ',';
                }
            }
            
            $this->_queryCache['joinColumns'] = $sql;
            return ', ' .trim($sql, ', ');
        }
        
        
        
        // build JOIN statement eg. LEFT JOIN pa_groups ON pa_groups.group_id = pa_users.primary_group
        if ($this->_queryCache['joinQuery'] and !$force)
            return $this->_queryCache['joinQuery'];
        
        foreach ($this->_joinColumns as $table)
        {
            // LEFT JOIN table ON
            $sql .= $table[0]. ' {$db_prefix}' .$table[1]. ' ON ';
            
            foreach ($table[2] as $left => $right)
            {
                $exp = explode(':', $left);
                
                if (count($exp) == 1)
                {
                    $exp[1] = $exp[0];
                    $exp[0] = "";
                }
                
                // AND table1.column = table2.column
                $sql .= $exp[0]. ' {$db_prefix}' .$table[1]. '.' .$exp[1]. ' = {$db_prefix}' .$this->_tableName. '.' .$right. ' ';
            }
        }
        
        $this->_queryCache['joinQuery'] = $sql;
        return $sql;
    }

    /*
     * Get generated types of queries, so you don't have to write it over and over again
     * 
     * @param string $type Type of query, can be: data, count, checkExists
     * @return string
     */

    public function getQuery($type='data')
    {
        switch ($type)
        {
            case 'data':
                return 'SELECT {$db_prefix}' .$this->_tableName. '.*' .$this->buildJoinQuery(1). ' FROM `{$db_prefix}' .$this->_tableName. '` ' .$this->buildJoinQuery();
            break;
            
            case 'count':
                return 'SELECT count(*) FROM `{$db_prefix}' .$this->_tableName. '`';
            break;
            
            case 'checkExists':
                return 'SELECT ' .$this->_idColumn. ' FROM `{$db_prefix}' .$this->_tableName. '`';
            break; 
        }
    }
    
    /**
      * Return data row in array or serialized array format
      *
      * @param bool $serialize Serialize before return?
      * @return array|string
      * @author Damian Kęska
      */
    
    public function getData($serialize=False)
    {
        if ($serialize)
            return serialize($this->_data);
            
        return $this->_data;
    }
    
    public static function fetchAll($by, $limit=0, $limitFrom=0, $order='id', $direction='DESC')
    {
        global $panthera;
        
        $c = get_called_class();
        $obj = new $c(null, null);
        $info = $obj->_getClassInfo();
        
        return $panthera->db->getRows($info['tableName'], $by, $limit, $limitFrom, $obj, $order, $direction);
    }

    /**
     * Build a multidimensional results tree
     * 
     * @param array $categories Array of categories with subcategories included in a 2 dimensional row
     * @param object|null $item Optional item to start looking for subitems
     * @param string $idColumn ID column
     * @param string $parentColumn Column where is stored parent id
     * @return array
     */

    public static function resultsToTree($categories, $item=null, $idColumn='id', $parentColumn='parent')
    {
        if ($item)
        {
            $map = array(
                'item' => $item, 
                'subcategories' => array(),
            );
            
            foreach ($categories as $key => $object)
            {
                if ($object -> get($parentColumn) == $item -> get($idColumn))
                {
                    $map['subcategories'][$object->get($idColumn)] = static::resultsToTree($categories, $object, $idColumn, $parentColumn);
                }
            }
            
            return $map;
        }
        
        $map = array();
        
        // find all items without category
        foreach ($categories as $key => $category)
        {
            if (!$category -> parent)
            {
                $map[$category -> get($idColumn)] = array(
                    'item' => $category,
                    'subcategories' => array(),
                );
            }
        }
        
        foreach ($map as $name => $attr)
        {
            $map[$name] = static::resultsToTree($categories, $attr['item'], $idColumn, $parentColumn);
        }
        
        return $map;
    }
    
    /**
     * Fetch items tree eg. categories -> subcategories -> subcategories -> ...
     *
     * @args pantheraFetchDB::fetchAll()
     * @see pantheraFetchDB::fetchAll()
     * @return array|bool
     * @author Damian Kęska
     */

    public static function fetchTree()
    {
        $c = get_called_class();
        $obj = new $c(null, null);
        $info = $obj->_getClassInfo();
        
        if (!$info['treeID'] or !$info['treeParent'])
            return FALSE;
        
        $results = call_user_func_array('static::fetchAll', func_get_args());

        return static::resultsToTree($results, null, $info['treeID'], $info['treeParent']);
    }
    
    /**
      * Constructor, here are logics that parses and loads all data, cache management etc.
      *
      * @param mixed $by
      * @param mixed $value
      * @return void 
      * @author Damian Kęska
      */

    public function __construct($by, $value)
    {
        global $panthera;
        $this->panthera = $panthera;
        $this->cacheGroup = get_class($this);
        
        // if it's just empty object
        if ($by === null)
        {
            return false;
        }
        
        // in case when we have other column identificator but want to use `id` to construct object
        if ($by == 'id' and $this->_idColumn != 'id')
        {
            $by = $this->_idColumn;
        }
        
        /**
          * Cache
          *
          */
        
        // get cache life time from database class
        if ($panthera->cacheType('cache') == 'memory' and $panthera->db->cache > 0 and $this->cache !== -1 and $this->cache !== False)
        {
            $this->cache = $panthera->db->cache;
        }
        
        // create a content cacheID, but at first check if caching is possible (we cant cache complicated objects like those constructed by array or object)
        if ($this->cache > 0 and is_string($by) and $by != 'array' and $this->panthera->cache)
        {    
            $this->cacheID = $panthera->db->prefix.$this->_tableName. '.' .serialize($by). '.' .$value;
        } else {
            $panthera -> logging -> output('Cache disabled for this ' .get_class($this). ' object', $this->cacheGroup);
        }   
         
        if ($this->cacheID)
        {
            if ($panthera->cache->exists($this->cacheID))
            {
                $panthera->logging->output('Found record in cache by id=' .$this->cacheID, $this->cacheGroup);
                $this->_data = $panthera->cache->get($this->cacheID);
                return True;
            }
        }
        
        // check if child class has met requirements - if the table name is provided
        if (!$this->_tableName)
            throw new Exception('$this->_tableName was not specified, cannot construct object of ' .get_class($this). ' extended by pantheraFetchDB');
            
        /**
          * Constructing object by array
          *
          */

        // construct object using existing data, so we dont have to make a SQL query again
        if ($by == 'array' and in_array('array', $this->_constructBy))
        {
            if ($panthera -> logging -> debug == True)
                $panthera -> logging -> output(get_class($this). '::Creating object from array ' .json_encode($value), $this->cacheGroup);
            
            // hooking
            $panthera -> get_options('pantheraFetchDB.' .get_class($this). '__construct', $this, $by);
            
            $this->_data = $value;
            $panthera -> add_option('session_save', array($this, 'save'));
            return False;
        } else {
            $SQL = NuLL;

            // get last result from DB
            if ($by == 'last_result')
            {
                $w = new whereClause();

                if (is_array($value))
                {
                    foreach ($value as $k => $v)
                    {
                        $w -> add( 'AND', $k, '=', $v);
                    }

                    $q = $w -> show();
                    $SQL = $panthera->db->query($this->getQuery(). ' WHERE '.$q[0]. ' ORDER BY `id` DESC LIMIT 0,1', $q[1]);
                } else
                    $SQL = $panthera->db->query($this->getQuery(). ' ORDER BY `id`');
            }
            
            /**
              * Constructing by multiple columns
              *
              */
            
            if (!$SQL and is_object($by))
            {
                if (get_class($by) != "whereClause")
                    throw new Exception('Input $by must be a whereClause object or a string with column name');
            
                $clause = $by->show();
                $SQL = $panthera->db->query($this->getQuery(). ' WHERE ' .$clause[0]. ' LIMIT 0,1', $clause[1]);
                //$by = $clause[0]; // caching object cannot be realized, its almost impossible
                //$value = $clause[1];
                
                if($panthera->logging->debug == True)
                    $panthera->logging->output(get_class($this). ':: Skipped cache in construction by object ' .$clause[0]. ' ' .json_encode($clause[1]), $this->cacheGroup);
            }
            
            /**
              * Constructing by column
              *
              */

            // if we dont have array to take fetched data we must fetch it by our own
            if (in_array($by, $this->_constructBy) and $SQL == NULL)
            {
                $SQL = $panthera->db->query($this->getQuery(). ' WHERE `' .$by. '` = :' .$by. ' LIMIT 0,1', array($by => $value));
            }

            // getting results and building a object
            if ($SQL != NULL)
            {
                if ($SQL -> rowCount() > 0)
                {
                    $this->_data = $SQL -> fetch(PDO::FETCH_ASSOC);
                    
                    // write to cache
                    $this -> updateCache();
                    
                    if($panthera->logging->debug == True)
                        $panthera->logging->output(get_class($this). '::Found a record by "' .json_encode($by). '" (value=' .json_encode($value). ')', $this->cacheGroup);

                    $panthera -> add_option('session_save', array($this, 'save'));

                } else {
                    if($panthera->logging->debug == True)
                        $panthera->logging->output(get_class($this). '::Cannot find record by "' .json_encode($by). '" (value=' .json_encode($value). ')', $this->cacheGroup);
                }
            }

            $panthera -> get_options('pantheraFetchDB.' .get_class($this). '__construct', $this, $by);
        }
    }
    
    /**
      * Clear this element cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clearCache($index=False)
    {
        if (!$this->exists())
        {
            return False;
        }
        
        if (!$this -> panthera -> cache or !$this->cacheID)
        {
            $this -> panthera -> logging -> output ('Cannot clear cache if cache is disabled', $this->cacheGroup);
            return False;
        }
        
        /*if (!$index)
        {
            $index = $this -> panthera -> cache -> get ($this -> panthera->db->prefix.$this->_tableName. '.index.' .$this->__get($this->_idColumn));
        }
        
        foreach ($index as $key)
        {
            $this -> panthera -> cache -> remove ($key);
            $this -> panthera -> logging -> output ('Clearing cache record, id=' .$key, 'pantheraFetchDB');
        }*/
        
        $index = $this -> panthera -> get_filters('pantheraFetchDB.' .get_class($this). '.clearCache', $index, True, $this);
        
        foreach ($this->_constructBy as $column)
        {
            if ($this->__get($column))
            {
                $cacheID = $this -> panthera->db->prefix.$this->_tableName. '.' .serialize($column). '.' .$this->__get($column);
                
                //if (in_array($cacheID, $index))
                //    continue;
                
                $this -> panthera -> cache -> remove($cacheID);
                $this -> panthera -> logging -> output ('Clearing cache record, id=' .$cacheID, $this->cacheGroup);
            }
        }
        
        return True;
    }
    
    /**
      * This function will completly update cache
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function updateCache()
    {
        // list of all cached versions of this item
        /*$cacheIndex = $this -> panthera->db->prefix.$this->_tableName. '.index.' .$this->__get($this->_idColumn);
        
        if (!$this->panthera->cache->exists($cacheIndex))
        {
            $index = array($this->cacheID);
            $this -> panthera->cache->set($cacheIndex, $index, $this->panthera->db->cache);
        } else {
        
            $index = $this -> panthera -> cache -> get ($cacheIndex);
            
            if (!in_array($this->cacheID, $index))
            {
                $index[] = array($this->cacheID);
                $this -> panthera->cache->set($cacheIndex, $index, $this->panthera->db->cache);
            }
        }*/
        
        if (!$this->cacheID)
            return False;
        
        $this -> clearCache();
        
        // update single record
        $this -> panthera -> cache -> set($this->cacheID, $this->_data, 'pantheraFetchDB_' .get_class($this));
        $this -> panthera->logging->output('Updated cache id=' .$this->cacheID, $this->cacheGroup);
    }
    
    /**
      * Check if object exists
      *
      * @return bool 
      * @author Damian Kęska
      */

    public function exists()
    {
        if ($this->_data != NuLL)
            return True;

        return False;
    }
    
    /**
      * Get data from column
      *
      * @param string $var Column name
      * @return mixed 
      * @author Damian Kęska
      */

    // Fast columns access from DB
    public function __get($var)
    {
        if(isset($this->_data[$var]))
            return $this->_data[$var];

        return False;
    }
    
    /**
      * Get raw data by column name
      *
      * @param string $key name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function getRaw($key)
    {
        if (isset($this->_data[$key]))
            return $this->_data[$key];
            
        return False;
    }
    
    /**
      * Set column data
      *
      * @param string $var Colum name
      * @param string $value Value
      * @return bool 
      * @author Damian Kęska
      */

    public function __set($var, $value)
    {
        // dont allow create new keys (because we will save those keys in database and we cant create new columns)
        if(!isset($this->_data[$var]))
        {
            $this->panthera->logging->output(get_class($this). '::Trying to set non-existing property "' .$var. '"', $this->cacheGroup);
            return False;
        }
        
        // if the variable already have save value as we are trying to set
        if ($this->_data[$var] == $value)
            return False;

        $this->_dataModified = True;        
        $this->_data[$var] = $value;
        $this->panthera->logging->output(get_class($this). '::set ' .$var. ' to ' .$value, $this->cacheGroup);
    }
    
    /**
     * Alias to __get
     * 
     * @author Damian Kęska
     * @param string $var Variable name
     * @return mixed
     */
    
    public function get($var)
    {
        return $this->__get($var);
    }
    
    /**
      * Save all changes to database
      *
      * @return void 
      * @author Damian Kęska
      */

    public function save()
    {
        global $panthera;

        if ($panthera == NuLL)
            $panthera = $this->panthera;
            
        if ($panthera->logging->debug == True)
            $panthera -> logging -> output ('Panthera Fetch DB class=' .get_class($this). ', changed data=' .print_r($this->_dataModified, True), $this->cacheGroup);
        
        $panthera -> get_options('pantheraFetchDB.' .get_class($this). '.save', $this);

        if($this->_dataModified and $this->_tableName)
        {
            $id = (integer)$this->_data[$this->_idColumn];

            $panthera->logging->output(get_class($this). '::Saving modified data ' .$this->_idColumn. '=' .$id, $this->cacheGroup);
            $copied = $this->_data;
            unset($copied[$this->_idColumn]);

            foreach ($this->_unsetColumns as $Key => $Value)
            {
                unset($copied[$Value]);
            }            

            $set = $panthera->db->dbSet($copied);
            $set[1][$this->_idColumn] = $id;
                  
            $SQL = $panthera->db->query('UPDATE `{$db_prefix}' .$this->_tableName. '` SET ' .$set[0]. ' WHERE `' .$this->_idColumn. '` = :' .$this->_idColumn. ';', $set[1]);
            
            // update cache
            $this -> clearCache();
            //$panthera->logging->output('Updated cache id=' .$this->cacheID, 'pantheraFetchDB');
            //$panthera->cache->set($this->cacheID, $this->_data, $panthera->db->cache);
                 
            $this->_dataModified = False;
        }
    }    
}

/**
  * PDOStatement extension
  *
  * @package Panthera\core\database
  * @author Damian Kęska
  */

class pantheraDBStatement extends PDOStatement
{
    protected $fetch = null;

    protected function __construct($PDO, $pantheraDB) 
    {  
        $this->driver = $pantheraDB->getSocketType();
    }

    /**
      * This function contains fix for SQLite3 driver where rowCount were not working
      *
      * @return int 
      * @author Damian Kęska
      */

    public function rowCount()
    {
        if ($this->driver == 'sqlite')
        {
            $this->fetch = $this->fetchAll();
            return count($this->fetch);
        } else
            return parent::rowCount();
    }

    /**
      * Fetching multiple rows
      *
      * @param int $how
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function fetchAll($how=PDO::FETCH_ASSOC, $class_name=PDO::FETCH_COLUMN, $ctor_args=1)
    {
        if ($this->fetch != null)
            return $this->fetch;
        else
            return parent::fetchAll($how);    
    }
    
    /**
      * Fetching single row
      *
      * @param string $how
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function fetch($how=PDO::FETCH_ASSOC, $class_name=PDO::FETCH_COLUMN, $ctor_args=1)
    {
        if ($this->fetch != null)
            return $this->fetch[0];
        else
            return parent::fetch($how);  
    }
}
