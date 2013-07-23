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

class pantheraDB
{
    public $sql, $prefix, $sqlCount=0, $cache=120;
    private $panthera, $socketType, $fixMissing=False;
    
    /**
      * Prepare database connection
      *
      * @param object $panthera
      * @config build_missing_tables
      * @return void 
      * @author Damian Kęska
      */

    public function __construct($panthera)
    {
        $this->panthera = $panthera;
        $config = $panthera->config->getConfig();
        
        $this->cache = intval(@$config['cache_db']);
       
        // this setting will automaticaly import database structures from template if any does not exists
        if (@$config['build_missing_tables'] == True)
            $this->fixMissing = True;

        try {
            // selecting between SQLite3 and MySQL database
            if (strtolower(@$config['db_socket']) == 'sqlite')
            {
                if (!is_file(SITE_DIR. '/content/database/' .$config['db_file']))
                    throw new Exception('Database fils is missing in /content/database/, please check config.php (variable - db_file) and file name');

                $this->socketType = 'sqlite';
                $this->sql = new PDO('sqlite:' .SITE_DIR. '/content/database/' .$config['db_file']);
                $this->sql->exec("pragma synchronous = off;");
                
                $panthera -> logging -> output('Connected to SQLite3 database file ' .$config['db_file'], 'pantheraDB');
            } else {
                $this->socketType = 'mysql';
                $this->sql = new PDO('mysql:host='.$config['db_host'].';encoding=utf8;dbname='.$config['db_name'], $config['db_username'], $config['db_password']);
                $panthera -> logging -> output('Connected to MySQL database, ' .$config['db_username']. '@' .$config['db_host'], 'pantheraDB');
            }

            $this->sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->prefix = $config['db_prefix'];


        } catch (Exception $e) {
            $this->_triggerErrorPage($e);
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

    public function _triggerErrorPage($e)
    {
        // a little bit hook to provide possibility to inform administrator about the error
        if (function_exists('userDBError'))
            userDBError($e);

        $message = $this->hideAuthInfo($e -> getMessage());
        $debugTemplate = getErrorPageFile('db_error');

        // if database error page exists
        if (is_file($debugTemplate))
        {
            include($debugTemplate);
            exit;        
        }

        // if not we will show simple error
        die('<h2>Server error</h2><br>Cannot connect to database: ' .$message);
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
      * @return object 
      * @author Damian Kęska
      */
    
    public function query($query, $values=NuLL)
    {
        $this->sqlCount++;
        $query = str_ireplace('{$db_prefix}', $this->prefix, $query);

        if ($this->panthera->logging->debug == True)
        {
            $this->panthera->logging->output('SQL::query( ' .$query. ' , ' .json_encode($values). ' )', 'pantheraDB');
        }

        // try to import missing tables if enabled
        if ($this->fixMissing == True)
        {
            try {
                $sth = $this->sql->prepare($query);

                if($values != NuLL)
                {
                    $this->bindArrayValue($sth, $values);
                }
            
                $sth->execute();
               
            } catch (PDOException $e) {
                if (($e -> getCode() == "42S02" and stristr($query, 'CREATE TABLE') === False) or ($e -> getCode() == "HY000" and stristr($query, 'CREATE TABLE') === False))
                {
                    preg_match("/'.*?'/", $e->getMessage(), $matches);
                 
                    // MySQL
                    if (count($matches) > 0)
                    {
                        $dbName = str_ireplace("'", '', str_ireplace($this->panthera->config->getKey('db_name'). '.', '', $matches[0])); // get only table name
                        $dbName = str_ireplace($this->prefix, '', $dbName); // remove prefix
                    } else {
                        // sqlite3                    
                        $dbName = explode('no such table: ', $e->getMessage());
                        $dbName = str_ireplace($this->prefix, '', $dbName[1]);
                    }
                    $file = getContentDir('/database/templates/' .$dbName. '.sql');

                    // debugging
                    $this->panthera->logging->output('Importing missing table "' .$dbName. '"', 'pantheraDB');
                    
                    if (is_file($file))
                    {
                        $SQL = file_get_contents($file);
                        $this->query($SQL);
                        return $this->query($query, $values);
                        
                    } else
                        throw new Exception($e->getMessage());
                
                } else
                    throw new Exception($e->getMessage());
            }
        } else {
            $sth = $this->sql->prepare($query);

            if($values != NuLL)
            {
                $this->bindArrayValue($sth, $values);
            }
                
            $sth -> execute();
        }
            
        return $sth;
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

    function getRows($db, $by, $limit, $offset, $returnAs='', $orderColumn='id', $order='DESC')
    {
        if (is_numeric($limit))
        {
            if (intval($limit) > 0)
            {
                $sqlLimit = ' LIMIT ' .intval($offset). ',' .intval($limit);
            }
        }
        
        $whereClause = '';

        if(is_array($by))
        {
            $w = new whereClause();

            foreach ($by as $k => $v)
            {
                $w -> add( 'AND', $k, '=', $v);
            }

            $q = $w -> show();
            $whereClause = ' WHERE ' .$q[0];
        }

        $what = '*';

        if (is_bool($limit))
            $what = '`id`';

        $SQL = $this->panthera->db->query('SELECT ' .$what. ' FROM `{$db_prefix}' .$db. '`'.$whereClause. ' ORDER BY `' .$orderColumn. '` ' .$order.@$sqlLimit, @$q[1]);
        

        $results = array();

        if (is_bool($limit))
        {
            return $SQL->rowCount();
        }    

        if ($SQL->rowCount() > 0)
        {
            $array = $SQL->fetchAll();

            foreach ($array as $item)
            {
                // return results as object
                if (class_exists($returnAs))
                    $results[] = new $returnAs('array', $item);
                else
                    $results[] = $item;
            }
        }

        return $results;
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
	protected $SQL=NuLL, $vals = array();

	public function add ( $Statement, $Column, $Equals, $Value )
	{
        $this->Values = array();
		$Equals_list = array ( '=' , '!=', '<', '>', '<=', '>=', 'LIKE' );

		if ( !in_array ( $Equals, $Equals_list ) ) //# Needle, haystack...
			return false;

		if ($Equals == 'LIKE')
			$Equals = ' LIKE '; // to be valid with syntax

		$Statement_list = array ( 'OR', 'AND', '', ',' );
	
		if ( !in_array ( $Statement, $Statement_list ) ) //# Needle, haystack...
			return false;

		if ( $this -> SQL == NuLL )
		{
			$Statement = '';	
		}

		$this->SQL .= $Statement. ' `' .$Column. '` ' .$Equals. ' :' .$Column. ' ';
        $this->vals[(string)$Column] = $Value;
		return true;
	}

	public function show ()
	{
		return array($this->SQL, $this->vals);
	}
}

/**
  * Panthera Fetch DB - Turning database results into object, a data model
  *
  * @package Panthera\core\database
  * @author Damian Kęska
  */

abstract class pantheraFetchDB
{
    protected $_dataModified = False; // save modifications to database?
    protected $_data = NuLL; // cache of database row
    protected $_tableName = NuLL; // table name
    protected $_idColumn = 'id';
    protected $_unsetColumns = array('created', 'modified', 'mod_time', 'last_result'); // columns we dont want to save
    protected $_constructBy = array('id', 'array');
    protected $panthera;
    protected $cache = 0;
    protected $cacheID = "";
    
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
        
        /**
          * Cache
          *
          */

        // in case when we have other column identificator but want to use `id` to construct object
        if ($by == 'id' and $this->_idColumn != 'id')
        {
            $by = $this->_idColumn;
        }
        
        if ($panthera->cacheType('cache') == 'memory' and $panthera->db->cache > 0)
            $this->cache = $panthera->db->cache;
        
        // caching
        if  ($this->cache > 0)
            $this->cacheID = $panthera->db->prefix.$this->_tableName. '.' .serialize($by);
        
        if ($this->cacheID != "")
        {
            if ($panthera->cache->exists($this->cacheID))
            {
                $panthera->logging->output('Found record in cache by id=' .$this->cacheID, 'pantheraFetchDB');
                $this->_data = $panthera->cache->get($this->cacheID);
                return True;
            }
        }
        
        if ($this->_tableName == NuLL)
            throw new Exception('$this->_tableName was not specified, cannot construct object of ' .get_class($this). ' extended by pantheraFetchDB');
            
        /**
          * Constructing object by array
          *
          */

        // construct object using existing data, so we dont have to make a SQL query again
        if ($by == 'array' and in_array('array', $this->_constructBy))
        {
            if ($panthera -> logging -> debug == True)
                $panthera -> logging -> output(get_class($this). '::Creating object from array ' .json_encode($value));
            
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
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}' .$this->_tableName. '` WHERE '.$q[0]. ' ORDER BY `id` DESC LIMIT 0,1', $q[1]);
                } else
                    $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}' .$this->_tableName. '` ORDER BY `id`');
            }
            
            /**
              * Constructing by multiple columns
              *
              */
            
            if ($SQL == NULL and is_object($by))
            {
                if (get_class($by) != "whereClause")
                    throw new Exception('Input $by must be a whereClause object or a string with column name');
            
                $clause = $by->show();
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}' .$this->_tableName. '` WHERE ' .$clause[0], $clause[1]);
            }
            
            /**
              * Constructing by column
              *
              */

            // if we dont have array to take fetched data we must fetch it by our own
            if (in_array($by, $this->_constructBy) and $SQL == NULL)
            {
                $SQL = $panthera->db->query('SELECT * FROM `{$db_prefix}' .$this->_tableName. '` WHERE `' .$by. '` = :' .$by, array($by => $value));
            }

            if ($SQL != NULL)
            {
                if ($SQL -> rowCount() > 0)
                {
                    $this->_data = $SQL -> fetch(PDO::FETCH_ASSOC);
                    
                    // write to cache
                    if ($panthera->cacheType('cache') == 'memory' and $panthera->db->cache > 0 and $this->cache == True)
                        $panthera->cache->set($this->cacheID, $this->_data, $panthera->db->cache);

                    if($panthera->logging->debug == True)
                        $panthera->logging->output(get_class($this). '::Found a record by ' .$by. ' (value=' .json_encode($value). ')');

                    $panthera -> add_option('session_save', array($this, 'save'));

                } else {
                    if($panthera->logging->debug == True)
                        $panthera->logging->output(get_class($this). '::Cannot find record by ' .$by. ' (value=' .json_encode($value). ')');
                }
            }
        }
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
        if(@array_key_exists($var, $this->_data))
            return $this->_data[$var];

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
        if (@!array_key_exists($var, $this->_data))
            return False;

        // if the variable already have save value as we are trying to set
        if ($this->_data[$var] == $value)
            return False;

        $this->_dataModified = True;        
        $this->_data[$var] = $value;
        $this->panthera->logging->output(get_class($this). '::set ' .$var. ' to ' .$value, 'pantheraFetchDB');
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
            $panthera -> logging -> output ('Panthera Fetch DB class=' .get_class($this). ', changed data=' .print_r($this->_dataModified, True), 'pantheraFetchDB');

        if($this->_dataModified == True and $this->_tableName != NuLL)
        {
            $id = (integer)$this->_data[$this->_idColumn];

            $panthera->logging->output(get_class($this). '::Saving modified data ' .$this->_idColumn. '=' .$id, 'pantheraFetchDB');
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
            if ($this->cacheID != "")
                $panthera->cache->set($this->cacheID, $this->_data, $panthera->db->cache);
                  
            $this->_dataModified = False;
        }
    }    
}
?>
