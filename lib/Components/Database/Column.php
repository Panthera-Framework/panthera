<?php
namespace Panthera\Components\Database;

/**
 * Class representing a column in a table
 *
 * @package Panthera\Components\Database
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class Column
{
    /** @var string $columnName */
    public $columnName;

    /** @var bool $isRawValue */
    public $isRawValue = false;

    /** @var string $value */
    public $value;

    /**
     * @param string $columnName
     * @param string $value
     * @param bool $isRawValue
     */
    public function __construct($columnName, $value, $isRawValue = false)
    {
        $this->columnName   = $columnName;
        $this->value        = $value;
        $this->isRawValue   = (bool)$isRawValue;
    }
}