<?php
namespace Panthera\Components\Database\Drivers;

use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Classes\BaseExceptions\DatabaseException;

use Panthera\Components\Database\Pagination;
use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Components\Database\DatabaseDriverInterface;

/**
 * Common code for database drivers based on PDO
 *
 * @package Panthera\Components\Database
 */
abstract class CommonPDODriver extends BaseFrameworkClass implements DatabaseDriverInterface
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
     * Configure a database socket right after connection was successful
     *
     * @slot framework.database.pdo.configure
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function configureSocket()
    {
        $config = $this->app->config->get('pdo');

        if ($config)
        {
            foreach ($config as $key => $value)
                $this->socket->setAttribute($key, $value);
        }

        $this->app->signals->execute('framework.database.pdo.configure', $this->socket);
    }

    /**
     * Check if connection to database was established
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function isConnected()
    {
        return ($this->socket instanceof \PDO); // weak check, but any idea?
    }

    /**
     * Start database transaction, turn off autocommit
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function createTransaction()
    {
        $this->socket->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function commit()
    {
        $this->socket->commit();
    }

    /**
     * Tell if we could use "LIMIT" statement when DELETING or UPDATING rows
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function deleteUpdateLimitsAvailable()
    {
        return false;
    }

    /**
     * Returns database type
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return string
     */
    public function getDatabaseType()
    {
        return 'unknown';
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
     * @param null|bool $execute Execute query or just return generated query and values
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function select($tableName, $what = null, $where = null, $order = null, $group = null, $limit = null, $values = array(), $joins = array(), $execute = true)
    {
        $query = 'SELECT ';

        /**
         * What
         */
        if ($what === null || $what == '*')
        {
            $query .= ' * ';

        }
        else
        {

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

        if ($execute)
        {
            return $this->query($query, $values);
        }

        return array($query, $values);
    }

    /**
     * Make a "DELETE" operation
     *
     * @param string $fromTableName Table name to operate on
     * @param null|array $where Where statement, an array, @see \Panthera\database::parseWhereConditionBlock() for example
     * @param null|array $values Values to pass/override
     * @param null|string|array $order Order by statement
     * @param null|Pagination $limit Deletion limit
     * @param bool $execute Execute or only return prepared SQL code?
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function delete($fromTableName, $where = null, $values = null, $order = null, $limit = null, $execute = true)
    {
        if (!is_array($values))
        {
            $values = [];
        }

        $query = 'DELETE FROM ' .$fromTableName;

        /**
         * Where
         *
         * @see \Panthera\database::parseWhereConditionBlock()
         */
        if ($where)
        {
            $whereBlock = $this->parseWhereConditionBlock($where, $fromTableName);
            $values = array_merge($values, $whereBlock['data']);
            $query .= ' WHERE ' .$whereBlock['sql'];
        }

        /**
         * Order by
         *
         * @see \Panthera\database::parseOrderByBlock()
         */
        if ($order)
        {
            $query .= ' ORDER BY ' .$this->parseOrderByBlock($order, $fromTableName);
        }

        /**
         * Limit
         */
        if ($limit)
        {
            throw new DatabaseException('LIMIT in DELETE is not supported in SQLite3 driver', 'FW_DB_DELETE_LIMIT_NOT_SUPPORTED');
        }

        if ($execute)
        {
            return $this->query($query, $values);
        }

        return array(
            'sql'    => $query,
            'values' => $values
        );
    }

    /**
     * Insert a new row into the database
     *
     * @param string $table Table name
     * @param array $data Columns and values array
     * @param bool $simulate Simulate and return query string instead of executing
     *
     * @throws DatabaseException
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function insert($table, array $data, $simulate = false)
    {
        $query = 'INSERT INTO `' .$table. '`';
        $columns = ' (';
        $values = ' VALUES (';

        foreach ($data as $key => $value)
        {
            $columns .= $key. ', ';
            $values .= ':' .$key. ', ';
        }

        $columns = rtrim($columns, ', '). ')';
        $values = rtrim($values, ', '). ')';

        $query .= $columns . $values;

        if ($simulate)
        {
            return [
                'query' => $query,
                'data'  => $data,
            ];
        }

        return $this->query($query, $data);
    }

    /**
     * Performs a row update
     *
     * @param string $table Table name
     * @param array $values Columns and values to set
     * @param null $where Optional where condition
     * @param null $limit Optional limit
     * @param bool $simulate Simulate and return query string instead of executing
     *
     * @throws DatabaseException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function update($table, array $values, $where = null, $limit = null, $simulate = false)
    {
        $query = 'UPDATE `' .$table. '` SET ';

        foreach ($values as $key => $value)
        {
            $query .= ' ' .$key. ' = :' .$key. ', ';
        }

        $query = rtrim($query, ', ');

        /**
         * Where
         *
         * @see \Panthera\database::parseWhereConditionBlock()
         */
        if ($where)
        {
            $whereBlock = $this->parseWhereConditionBlock($where, $table);
            $values = array_merge($values, $whereBlock['data']);
            $query .= ' WHERE ' .$whereBlock['sql'];
        }

        /**
         * Limit
         */
        if ($this->deleteUpdateLimitsAvailable())
        {
            throw new DatabaseException('LIMIT in UPDATE is not supported in ' .$this->getDatabaseType(). ' driver', 'FW_DB_UPDATE_LIMIT_NOT_SUPPORTED');
        }

        if ($simulate)
        {
            return [
                'query' => $query,
                'data'  => $values,
            ];
        }

        return $this->query($query, $values);
    }

    /**
     * Make a SQL query and return resultset
     *
     * @param string $query
     * @param array $values
     *
     * @throws PantheraFrameworkException
     * @throws DatabaseException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return bool|array
     */
    public function query($query, $values = array())
    {
        /**
         * In debugging mode log all queries
         */
        if ($this->app->isDeveloperMode())
        {
            $this->app->logging->output('Executing query: ' .$query. ', data: ' .json_encode($values, JSON_PRETTY_PRINT), 'debug');
        }

        try
        {
            $sth = $this->socket->prepare($query);
        }
        catch (\PDOException $e)
        {
            throw new DatabaseException($e->getMessage() . ', SQL: ' . $query, 'PDO_EXCEPTION');
        }


        if (is_array($values) && $values)
        {
            foreach ($values as $k => $v)
            {
                if (is_array($v))
                {
                    throw new DatabaseException('Value must be a string, not array in configuration.', 'FW_DATABASE_QUERY_FAILED');
                }

                $sth->bindValue($k, $v);
            }
        }

        try
        {
            $response = $sth->execute();

            if (substr($query, 0, 6) == 'SELECT')
                $response = $sth->fetchAll(\PDO::FETCH_ASSOC);

            $sth->closeCursor();
        }
        catch (\PDOException $e)
        {
            $this->app->logging->output('Got a PDO exception ' .serialize($e), 'debug');
            throw new PantheraFrameworkException('Got a PDO exception: ' .$e->getMessage(). ', SQL: ' .$query, 'FW_DATABASE_QUERY_FAILED');
        }

        return $response;
    }

    /**
     * Parse "WHERE" condition block - from PHP array to SQL string
     *
     * Example of input:
     * array(
     *   array(
     *      '|=|name' => 'test',
     *      '|AND|=|title' => 'aaa',
     *   ),
     *
     *   '|OR|.test' => array(
     *      array(
     *          '|=|a' => 'other test',
     *          '|OR|b' => 'other title',
     *      ),
     *
     *      '|AND|.' => array(
     *          '|[]|unique' => array(
     *              'first', 'second', 'third',
     *          )
     *      ),
     *   ),
     * );
     *     *
     * @param array $whereCondition The condition
     * @param string|null $columnNamePrefix Optional prefix to add to every column name (in case column don't have any)
     * @param bool $isJoin Is this a where condition for JOIN clause?
     *
     * @throws PantheraFrameworkException
     * @return array
     */
    public function parseWhereConditionBlock($whereCondition, $columnNamePrefix = null, $isJoin = false)
    {
        $output = '';
        $values = array();
        $logicOperatorAllowed = false;

        foreach ($whereCondition as $condition => $value)
        {
            $splitted = explode('|', $condition);
            $len = count($splitted);

            if ($len > 4 && !is_numeric($condition))
            {
                throw new PantheraFrameworkException('Where conditions could have only maximum 3 blocks, given ' .count($splitted). ', details: "' .$condition. '"', 'FW_SQL_CONDITIONS_INVALID');
            }

            /**
             * Step 1: Logic operators - AND/OR
             */
            if ($logicOperatorAllowed)
            {
                $logicOperator = 'AND';

                if (!is_numeric($condition))
                {
                    for ($i = 1; $i <= 2; $i++)
                    {
                        if ($splitted[$i] == 'OR' || $splitted[$i] == 'AND' || $splitted[$i] == 'XOR')
                        {
                            $logicOperator = $splitted[1];
                            break;
                        }
                    }
                }

                $output .= ' ' .$logicOperator;
            }

            // inherited conditions
            if ($len === 3 && substr($splitted[2], 0, 1) === '.' || is_numeric($condition))
            {
                $subCondition = $this->parseWhereConditionBlock($value, $columnNamePrefix, $isJoin);
                $output .= ' ' .$subCondition['sql']. ' ';
                $values = array_merge($values, $subCondition['data']);
                $logicOperatorAllowed = true;
                continue;
            }

            $columnName = ($len === 4 ? $splitted[3] : $splitted[2]);

            if ($value instanceof Column)
            {
                $columnId = $value->columnName;
            }
            else
            {
                $columnId = $columnName . '_' . substr(hash('md4', rand(0, 9) . microtime(true)), 0, 8);
            }

            // append a column name prefix on columns that don't have any
            if ($columnNamePrefix && strpos($columnName, '.') === false)
            {
                $columnName = $columnNamePrefix. '.' .$columnName;
            }

            /**
             * Step 2: Comparison operators
             */
            $comparisonOperator = '=';

            for ($i = 1; $i <= 2; $i++)
            {
                if (isset($this->comparisonOperators[$splitted[$i]]))
                {
                    $comparisonOperator = $this->comparisonOperators[$splitted[$i]];
                    break;
                }
            }

            if ($comparisonOperator == 'in' || $comparisonOperator == 'not in')
            {
                $output .= ' ' .$columnName . ' ' .$comparisonOperator. ' (';

                foreach ($value as $i => $k)
                {
                    $output .= ':' .$columnId. '_' .$i. ', ';
                    $values[$columnId. '_' .$i] = $k;
                }

                $output = rtrim($output, ', '). ')';


            } else {
                $output .= ' ' . $columnName . ' ' . $comparisonOperator. ' ';

                if (!$columnId)
                {
                    $output .= $columnNamePrefix . '.' . $value. ' ';
                } else {
                    $output .= ':' . $columnId . ' ';
                    $values[$columnId] = $value;
                }
            }

            $logicOperatorAllowed = true;
        }

        return array(
            'sql' => '(' .$output. ')',
            'data' => $values,
        );
    }

    /**
     * Example:
     *
     * JOIN|group => WHERE CLAUSE
     *
     * @param array $joins List of joined tables
     * @param string $mainTable
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function parseJoinConditionBlock(array $joins, $mainTable)
    {
        $SQL = '';

        foreach ($joins as $joinedTable => $whereClause)
        {
            $exp = explode('|', $joinedTable);
            $SQL .= $exp[0]. ' ' .$exp[1]. ' ON ' .$this->parseWhereConditionBlock($whereClause, $mainTable, true)['sql']. ' ';
        }

        return $SQL;
    }

    /**
     * List of columns to order by
     *
     * eg. array('userName DESC', 'userId ASC')
     *
     * @param array $orderBy List of columns and sorting directions
     * @param string|null $columnNamePrefix Optional column name prefix
     *
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function parseOrderByBlock($orderBy, $columnNamePrefix = null)
    {
        $result = '';

        // wrap it to support like array
        if (is_string($orderBy))
        {
            $orderBy = array($orderBy);
        }

        foreach ($orderBy as $orderColumn)
        {
            $exp = explode(' ', $orderColumn);

            if (count($exp) > 2)
            {
                throw new PantheraFrameworkException('Cannot parse orderBy block "' .$orderColumn. '", invalid syntax. Syntax example: "userName DESC"', 'FW_SQL_CONDITIONS_ORDERBY');
            }

            // append default sorting
            if (!isset($exp[1]))
            {
                $exp[1] = 'ASC';
            }

            if ($exp[1] !== 'ASC' && $exp[1] !== 'DESC')
            {
                throw new PantheraFrameworkException('Order by block has invalid sorting direction value, possible values: ASC, DESC or empty', 'FW_SQL_CONDITIONS_ORDERBY_DIRECTION');
            }

            // append an optional column prefix if not any found in column name
            if ($columnNamePrefix && strpos($exp[0], '.') === false)
            {
                $exp[0] = $columnNamePrefix. '.' .$exp[0];
            }

            $result .= $exp[0]. ' ' .$exp[1]. ', ';
        }

        return rtrim($result, ', ');
    }

    /**
     * Parse array into "GROUP BY" statement (common for SQLite3 and MySQL database types)
     *
     * Example:
     *   array(
     *      'userName',
     *      'count(userId)',
     *   );
     *
     * @param array|string $columnsList List of columns, see example
     * @param string|null $columnNamePrefix Optional column name prefix
     *
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function parseGroupByBlock($columnsList, $columnNamePrefix = null)
    {
        $result = '';

        if (is_string($columnsList))
        {
            $columnsList = array($columnsList);
        }

        foreach ($columnsList as $column)
        {
            // GROUP BY count(name)
            if (strpos($column, '(') !== false)
            {
                $exp = explode('(', $column);

                if (!isset($this->functions[strtolower($exp[0])]))
                {
                    throw new PantheraFrameworkException('Parser error: Unrecognized SQL function "' .$exp[0]. '" for used database handler', 'FW_SQL_UNRECOGNIZED_FUNCTION');
                }

                if ($columnNamePrefix && strpos($exp[1], '.') === false)
                {
                    $exp[1] = $columnNamePrefix. '.' .$exp[1];
                }

                $column = implode('(', $exp);
            } else {
                if ($columnNamePrefix && strpos($column, '.') === false)
                {
                    $column = $columnNamePrefix. '.' .$column;
                }
            }

            $result .= $column. ', ';
        }

        return rtrim($result, ', ');
    }
}