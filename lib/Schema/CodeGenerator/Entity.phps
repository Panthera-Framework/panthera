<?php
namespace {$namespace$};

use {$extends$};

/**
 * {$projectName$}
 * ---------------
 * Class {$className$}
 */
class {$className$} extends {$baseNameExtends$}
{
    protected static $__ORM_Table = '{$tableName$}';
    protected static $__ORM_IdColumn = '{$idColumn$}';

    /**
     * @orm
     * @column {$idColumn$}
     * @var integer
     */
    protected ${$idProperty$} = null;
}