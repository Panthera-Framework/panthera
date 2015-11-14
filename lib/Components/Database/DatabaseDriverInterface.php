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
    public function connect();
    public function query($query, $values);
    //public function select();
    //public function insert();
    //public function update();
}