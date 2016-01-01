<?php
namespace Panthera\Components\Database;

/**
 * Interface databaseHandlerInterface
 * Every database handler must implement this interface and keep the standards
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Components\Database
 */
interface DatabaseDriverInterface
{
    /**
     * Checks if table exists
     *
     * @param string $table
     * @return bool
     */
    public function hasTable($table);

    /**
     * @return bool
     */
    public function connect();

    /**
     * Send a SQL query
     *
     * @param string $query
     * @param array $values
     * @return bool
     */
    public function query($query, $values);
    //public function select();
    //public function insert();
    //public function update();
}