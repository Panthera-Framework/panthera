<?php
namespace Panthera\Components\Database\Operations;
use Panthera\Components\Kernel\Framework;

/**
 * A objective wrapper for creating SELECT database queries
 *
 * @package Panthera\database
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class Select
{
    public $what = null;

    public $table = null;

    public $where = null;

    public $joins = array();

    public $order = null;

    public $group = null;

    public $limit = null;

    public $values = array();

    /**
     * Constructor
     *
     * @param string $table Table name
     * @param string|array $what List of columns, or null to insert '*'
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct($table, $what = null)
    {
        $this->what = $what;
        $this->table = $table;
    }

    /**
     * Execute prepared query
     *
     * @param bool $execute Execute query or simulate execution?
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function execute($execute = true)
    {
        $fw = Framework::getInstance();
        return $fw->database->select(
            $this->table,
            $this->what,
            $this->where,
            $this->order,
            $this->group,
            $this->limit,
            $this->values,
            $this->joins,
            $execute
        );
    }
}