<?php

namespace Panthera\database;

require_once 'DatabaseHandler.class.php';

/**
 * MySQL database handler for Panthera Framework 2
 *
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 * @package Panthera\database\mysql
 */
class MySQLDatabaseHandler extends DatabaseHandler
{
    /**
     * Connect to a MySQL/MariaDB database
     * Creates PDO connection to the MySQL server
     *
     * @throws \Panthera\PantheraFrameworkException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function connect()
    {
        $this->socket = @new \PDO('mysql:host='.$this->app->config->data['database']['host'].';encoding=utf8;charset=utf8;dbname='.$this->app->config->data['database']['name'], $this->app->config->data['database']['user'], $this->app->config->data['database']['password'],
            array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => intval($this->app->config->data['database']['timeout']),
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            )
        );

        if (isset($this->app->config->data['database']['mysql_buffered_queries']))
            $this->socket->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, (bool)$this->app->config->data['database']['mysql_buffered_queries']);
    }
}