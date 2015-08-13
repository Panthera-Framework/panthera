<?php
namespace Panthera\database;

/**
 * SQLite3 database handler for Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\database\sqlite3
 */
class SQLite3DatabaseHandler extends driver implements databaseHandlerInterface
{
    /**
     * PDO object
     *
     * @var \PDO $socket
     */
    public $socket = null;

    /**
     * List of translated SQL functions
     *
     * @var array
     */
    public $functions = array(
        'count' => 'COUNT',
        'avg'   => 'AVG',
        'max'   => 'MAX',
        'min'   => 'MIN',
        'sum'   => 'SUM',
    );

    /**
     * List of operators translated to SQL syntax
     *
     * @var array
     */
    public $comparisonOperators = array(
        '=' => '=',
        '!' => '<>',
        '<>' => '<>',
        'in' => 'in',
        '[]' => 'in',
        '!in' => 'not in',
        '![]' => 'not in',
        '~' => 'like',
        'like' => 'like',
    );

    /**
     * Connect to a database
     * Creates a /.content/database.sqlite3 file that will store tables
     *
     * @throws \Panthera\FileException
     */
    public function connect()
    {
        if (!is_writable($this->app->appPath. '/.content/'))
        {
            throw new \Panthera\FileException('Path "' .$this->app->appPath. '/.content/" is not writable', 'FW_CONTENT_NOT_WRITABLE');
        }

        $this->socket = new \PDO('sqlite:' .$this->getDatabasePath());
        $this->socket->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Returns database path
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function getDatabasePath()
    {
        // database configuration from app.php
        $dbConfig = $this->app->config->get('database');

        return $this->app->appPath. '/.content/' .(isset($dbConfig['name']) ? $dbConfig['name'] : 'database'). '.sqlite3';
    }

    /**
     * Returns database type
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function getDatabaseType()
    {
        return 'sqlite3';
    }

    /**
     * Make a SELECT operation on database table
     *
     * @param string $tableName Table name for SELECT operation
     * @param null|array $what Null means '*' (all columns), example array of columns: array('userId', 'userName', 'userLogin')
     * @param null|array $where Where statement, an array, @see \Panthera\database::parseWhereConditionBlock() for example
     * @param null|string|array $order Order by statement
     * @param null|string|array $group Group by those columns
     * @param null|Pagination $limit
     * @param null|array $values Optional values
     * @param null|array $joins Joined tables
     *
     * @throws \Panthera\PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function select($tableName, $what = null, $where = null, $order = null, $group = null, $limit = null, $values = array(), $joins = array())
    {
        $query = 'SELECT ';

        /**
         * What
         */
        if ($what === null || $what == '*')
        {
            $query .= ' * ';

        } else {

            foreach ($what as $item)
            {
                if (strpos($item, '.') === false)
                {
                    $prefix = 's1.';
                } else {
                    $prefix = '';
                }

                $query .= $prefix . $item . ', ';
            }

            $query = rtrim($query, ', ');
        }

        $query .= ' FROM `' .$tableName. '` as s1 ';

        /**
         * SQL joins
         *
         * @see \Panthera\database::parseJoinConditionBlock()
         */
        if ($joins)
        {
            $query .= $this->parseJoinConditionBlock($joins, 's1');
        }

        /**
         * Where
         *
         * @see \Panthera\database::parseWhereConditionBlock()
         */
        if ($where)
        {
            $whereBlock = $this->parseWhereConditionBlock($where, 's1');
            $values = array_merge($values, $whereBlock['data']);
            $query .= 'WHERE ' .$whereBlock['sql'];
        }

        /**
         * Order by
         *
         * @see \Panthera\database::parseOrderByBlock()
         */
        if ($order)
        {
            $query .= ' ORDER BY ' .$this->parseOrderByBlock($order, 's1');
        }

        /**
         * Group by
         *
         * @see \Panthera\database::parseGroupByBlock()
         */
        if ($group)
        {
            $query .= ' GROUP BY ' .$this->parseGroupByBlock($group, 's1');
        }

        /**
         * Pagination - LIMIT and OFFSET
         *
         * @see \Panthera\database\Pagination
         */
        if ($limit && $limit instanceOf Pagination)
        {
            $limit = $limit->getSQLData();
            $query .= ' LIMIT ' .$limit[1]. ' OFFSET ' .$limit[0]. ' ';
        }

        return array($query, $values);
    }

    /**
     * Make a SQL query and return resultset
     *
     * @param string $query
     * @param array $values
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function query($query, $values)
    {
        $sth = $this->socket->prepare($query);

        foreach ($values as $k => $v)
        {
            $sth->bindParam(':' .$k, $v);
        }

        $sth->execute();
        $fetch = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $sth->closeCursor();

        return $fetch;
    }
}