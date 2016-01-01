<?php
namespace Panthera\Components\Orm;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Classes\BaseExceptions\InvalidDefinitionException;
use Panthera\Classes\Utils\ClassUtils;
use Panthera\Classes\Utils\StringUtils;
use Panthera\Components\Database\Column;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Orm\ORMBaseFrameworkObject;

/**
 * Panthera Framework 2
 * --------------------
 * Meta data provider
 *
 * @package Panthera\Components\Orm
 */
class ORMMetaDataProvider
{
    /**
     * @param ORMBaseFrameworkObject $instance
     * @return array
     */
    public static function getColumnsMapping(ORMBaseFrameworkObject $instance)
    {
        $cache = Framework::getInstance()->cache;
        $cacheKey = 'ORMMetaData-columns-' . get_class($instance);

        if ($cache->get($cacheKey))
        {
            return $cache->get($cacheKey);
        }

        $results = [];
        $reflection = new \ReflectionClass($instance);

        foreach ($reflection->getProperties() as $property)
        {
            $phpDoc = $property->getDocComment();
            $columnMetaPos = strpos($phpDoc, '@column ');

            if ($columnMetaPos !== false)
            {
                $column = substr($phpDoc, ($columnMetaPos + 8), (strpos($phpDoc, "\n", $columnMetaPos) - $columnMetaPos) - 8);

                if ($column)
                {
                    $results[$column] = $property->getName();
                }
            }
        }

        $cache->set($cacheKey, $results, 300);
        return $results;
    }

    /**
     * Detect join name from @orm string
     *
     * @param string $ormString
     * @return mixed
     */
    public static function getJoinType($ormString)
    {
        $joins = [
            'leftJoin'      => 'LEFT JOIN',
            'rightJoin'     => 'RIGHT JOIN',
            'outerJoin'     => 'OUTER JOIN',
            'innerJoin'     => 'INNER JOIN',
            'leftOuterJoin' => 'LEFT OUTER JOIN',
        ];

        foreach ($joins as $joinName => $syntax)
        {
            if (strpos($ormString, $joinName) !== false)
            {
                return $syntax;
            }
        }

        return $joins['leftJoin'];
    }

    /**
     * Parses PHPDoc looking for tables to join
     *
     * @param ORMBaseFrameworkObject $instance
     *
     * @throws InvalidDefinitionException
     * @throws \Exception
     *
     * @return array
     */
    public static function getJoinsData(ORMBaseFrameworkObject $instance)
    {
        $cache = Framework::getInstance()->cache;
        $cacheKey = 'ORMMetaData-joins-' . get_class($instance);

        if ($cache->get($cacheKey))
        {
            return $cache->get($cacheKey);
        }

        $columnMeta = [];
        $joinColumns = [];
        $joins = [];
        $reflection = new \ReflectionClass($instance);

        foreach ($reflection->getProperties() as $property)
        {
            $joinType = ClassUtils::getTag($property->getDocComment(), 'orm');

            if ($joinType)
            {
                $joinType = static::getJoinType($joinType[0]);
            }

            $tag = ClassUtils::getTag($property->getDocComment(), 'join');

            if (!$tag || !is_string($joinType))
            {
                continue;
            }

            // parses string eg. @join "test.id" => "column1, column2 as aliasedColumn"
            $starts   = 0;
            $offset   = 0;
            $modifier = null;

            $tag       = $tag[0];
            $condition = StringUtils::getString($tag, $offset, $starts, $modifier);
            $offset++; // offset is passed by reference, so it need to be initialized and increased
            $columns   = StringUtils::getString($tag, $offset);

            if (!$condition || !$columns)
            {
                throw new InvalidDefinitionException('Invalid table join definition for ' . $property->getName(), 'INVALID_COLUMN_DEFINITION');
            }

            if ($modifier !== 'r')
            {
                list($table, $column) = explode('.', $condition);

                $joins[$joinType. '|' . $table] = [
                    '|=|' . $table . '.' . $column => new Column($column, $instance->getId(true), true),
                ];

            } else {
                $table = $columns;
                $offset++;
                $columns = StringUtils::getString($tag, $offset);

                $joins[$joinType. '|' . $table] = $condition;
            }

            // index all columns to use in SELECT clause, and collect information where they come from
            $columnsParts = explode(',', $columns);
            $columns = [];

            foreach ($columnsParts as $part)
            {
                $aliasName = $name = $part;
                $alias = strpos($part, ' as ');

                if ($alias)
                {
                    $aliasName = substr($part, $alias + 4);
                    $name = substr($part, 0, $alias);
                }

                $aliasName = trim($aliasName);
                $columns[$table . '.' . trim($name)] = $aliasName;
                $columnMeta[$aliasName] = [
                    'table' => $table,
                    'name'  => trim($name),
                ];
            }

            $joinColumns = array_merge($joinColumns, $columns);
        }

        return [
            /* List of joins to use in JOIN clause */
            $joins,

            /* List of columns to use in SELECT */
            $joinColumns,

            /* List of aliases with explanation where they came from - table and column */
            $columnMeta,
        ];
    }
}