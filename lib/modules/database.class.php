<?php
namespace Panthera;

class database extends baseClass
{
    public $object = null;

    public static function getDatabaseInstance($databaseType)
    {
        $framework = framework::getInstance();
        $path = $framework->getPath('modules/databaseHandlers/' .$databaseType. 'DatabaseHandler.class.php');
        $className = '\\Panthera\\' .$databaseType. 'DatabaseHandler';

        require $path;

        if (!in_array('Panthera\databaseHandlerInterface', class_implements($className)))
        {
            throw new \Panthera\PantheraFrameworkException('Database handler "' .$className. '" have to implement "databaseHandlerInterface" interface', 'FW_INVALID_DRIVER');
        }

        $object = new $className;
        $object->connect();

        return $object;
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
     *
     * @param array $whereCondition The condition
     * @param string|null $columnNamePrefix Optional prefix to add to every column name (in case column don't have any)
     * @return array
     * @throws PantheraFrameworkException
     */

    public function parseWhereConditionBlock($whereCondition, $columnNamePrefix = null)
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
                $subCondition = $this->parseWhereConditionBlock($value);
                $output .= ' ' .$subCondition['sql']. ' ';
                $values = array_merge($values, $subCondition['data']);
                $logicOperatorAllowed = true;
                continue;
            }

            $columnName = ($len === 4 ? $splitted[3] : $splitted[2]);
            $columnId = $columnName . '_' . substr(hash('md4', rand(0, 9) . microtime(true)), 0, 8);

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

    /**
     * List of columns to order by
     *
     * eg. array('userName DESC', 'userId ASC')
     *
     * @param $orderBy
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
}

interface databaseHandlerInterface
{
    public function connect();
    //public function select();
    //public function insert();
    //public function update();
}