<?php
namespace Panthera\database\schemaParser;

/**
 * This class parses input array (taken from YAML file for example) and creates SQL "CREATE TABLE" statements
 * It's specific for SQLite3 database type, for MySQL please check proper class
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\database\schemaParser
 */
class SQLite3DatabaseSchemaParser extends BaseDatabaseSchemaParser
{
    protected $columnTypes = [
        'integer', 'text', 'boolean', 'date',
    ];

    /**
     * Parse column attributes and generate a code
     *
     * @param string $table Table
     * @param string $column Column
     * @param string $attributes Column attributes eg. type, length, index, null
     *
     * @throws SchemaParsingException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    protected function parseAttributes($table, $column, $attributes)
    {
        $code = '';

        // type is necessary
        if (!isset($attributes['type']))
        {
            throw new SchemaParsingException('A column has to have a type! Error at ' .$table. '.' .$column, 'DB_SCHEMA_NO_COLUMN_TYPE');
        }

        // check if type is supported by SQLite3 schema parser
        if (!in_array($attributes['type'], $this->columnTypes))
        {
            throw new SchemaParsingException('Column ' .$table. '.' .$column. ' has unsupported type "' .$attributes['type']. '" by ' .get_called_class(), 'DB_SCHEMA_UNSUPPORTED_TYPE');
        }

        // column TYPE
        $code .= $column. ' ' .strtoupper($attributes['type']);

        // length
        if (isset($attributes['length']) && intval($attributes['length']) > 0)
        {
            $code .= '(' .(string)$attributes['length']. ')';
        }

        // null/not null
        if (!isset($attributes['isNull']) || !$attributes['isNull'])
        {
            $code .= ' NOT NULL';
        }

        // key
        if (isset($attributes['key']))
        {
            switch ($attributes['key'])
            {
                case 'primary':
                    $code .= ' PRIMARY KEY';
                break;

                case 'unique':
                    $code .= ' UNIQUE KEY';
                break;
            }
        }

        // autoincrement
        if (isset($attributes['autoincrement']) && $attributes['autoincrement'])
        {
            $code .= ' AUTOINCREMENT';
        }

        return $code;
    }

    /**
     * Generate initial schema, what means database structure - create table statements
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @throws SchemaParsingException
     */
    public function generateInitialSchema()
    {
        $code = '';

        foreach ($this->schema as $table => $columns)
        {
            $cNum = 0;
            $code .= "-- Generated from YAML with Panthera Framework 2 schema parser\n";
            $code .= "-- Date: " .date('Y-m-d H:i:s'). "\n";
            $code .= "CREATE TABLE " .$table. "\n(\n";

            foreach ($columns as $column => $attributes)
            {
                $cNum++;
                $code .="\t";
                $code .= $this->parseAttributes($table, $column, $attributes);
                if ($cNum < count($columns)) $code .= ",";
                $code .= "\n";
            }

            $code = rtrim($code, ',');
            $code .= ");\n";
        }

        return $code;
    }
}