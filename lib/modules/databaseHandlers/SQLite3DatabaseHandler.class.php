<?php
namespace Panthera;

class SQLite3DatabaseHandler extends \Panthera\database implements databaseHandlerInterface
{
    /**
     * @var \PDO $socket
     */
    public $socket = null;

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

    public function select($tableName, $what = null, $where = null, $order = null, $limit = null)
    {
        $query = 'SELECT ';
        $where = '';

        if ($what === null)
        {
            $where .= ' * ';
        } else {

            foreach ($what as $item)
            {
                if (strpos($item, '.') === false)
                {
                    $prefix = 's1.';
                } else {
                    $prefix = '';
                }

                $where .= $prefix . $item . ', ';
            }

            $where = rtrim($where, ', ');
        }

        $where .= ' FROM `' .$tableName. '` as s1 ';
        $query .= $where;

        $t = array(
            '|=|name' => 'test',
            '|AND|=|title' => 'aaa',

            '|OR|.' => array(
                '|=|name' => 'other test',
                '|AND|title' => 'other title',
                '|OR|.' => array(
                    '|[]|unique' => array(
                        'first', 'second', 'third',
                    )
                )
            ),
        );

        var_dump($this->parseWhereConditionBlock($t));
    }

    public function parseWhereConditionBlock($whereCondition)
    {
        $output = '';
        $values = array();
        $logicOperatorAllowed = false;

        foreach ($whereCondition as $condition => $value)
        {
            $splitted = explode('|', $condition);
            $len = count($splitted);

            if ($len > 4)
            {
                throw new PantheraFrameworkException('Where conditions could have only maximum 3 blocks, given ' .count($splitted). ', details: "' .$condition. '"', 'FW_SQL_CONDITIONS_INVALID');
            }

            // data
            $columnName = ($len === 4 ? $splitted[3] : $splitted[2]);
            $columnId = $columnName. '_' .substr(hash('md4', rand(0, 9) . microtime(true)), 0, 8);

            /**
             * Step 1: Logic operators - AND/OR
             */
            $logicOperator = 'AND';

            if ($logicOperatorAllowed)
            {
                for ($i = 1; $i <= 2; $i++)
                {
                    if ($splitted[$i] == 'OR' || $splitted[$i] == 'AND')
                    {
                        $logicOperator = $splitted[1];
                        break;
                    }
                }

                $output .= ' ' .$logicOperator;
            }

            if ($len === 3 && substr($splitted[2], 0, 1) === '.')
            {
                $subCondition = $this->parseWhereConditionBlock($value);
                $output .= ' ' .$subCondition['sql']. ' ';
                $values = array_merge($values, $subCondition['data']);
                continue;
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
                $output .= ' ' .$columnName . ' ' .$comparisonOperator. ' :' .$columnId. ' ';
                $values[$columnId] = $value;
            }

            $logicOperatorAllowed = true;
        }

        return array(
            'sql' => '(' .$output. ')',
            'data' => $values,
        );
    }
}