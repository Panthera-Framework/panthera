<?php
namespace Panthera;

class SQLite3DatabaseHandler extends \Panthera\database implements databaseHandlerInterface
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
     *
     * @throws FileException
     */
    public function connect()
    {
        if (!is_writable($this->app->appPath. '/.content/'))
        {
            throw new FileException('Path "' .$this->app->appPath. '/.content/" is not writable', 'FW_CONTENT_NOT_WRITABLE');
        }

        $this->socket = new \PDO('sqlite:' .$this->app->appPath. '/.content/database.sqlite3');
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
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function select($tableName, $what = null, $where = null, $order = null, $group = null, $limit = null, $values = array(), $joins = array())
    {
        $query = 'SELECT ';

        /**
         * What
         */
        if ($what === null)
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

        return $query;
    }
}