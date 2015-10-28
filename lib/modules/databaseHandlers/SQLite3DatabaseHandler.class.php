<?php

namespace Panthera\database;

require_once 'DatabaseHandler.class.php';

/**
 * SQLite3 database handler for Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\database\sqlite3
 */
class SQLite3DatabaseHandler extends DatabaseHandler
{
    /**
     * Connect to a database
     * Creates a /.content/database.sqlite3 file that will store tables
     *
     * @throws \Panthera\FileException
     */
    public function connect()
    {
        if (!is_writable($this->app->appPath. '/.content/'))
        {
            throw new \Panthera\FileException('Path "' .$this->app->appPath. '/.content/" is not writable', 'FW_CONTENT_NOT_WRITABLE');
        }

        $this->socket = new \PDO('sqlite:' .$this->getDatabasePath());
        $this->socket->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->socket->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $this->configureSocket();
    }

    /**
     * Returns database path
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function getDatabasePath()
    {
        // database configuration from app.php
        $dbConfig = $this->app->config->get('database');

        return $this->app->appPath. '/.content/' .(isset($dbConfig['name']) ? $dbConfig['name'] : 'database'). '.sqlite3';
    }

    /**
     * Returns database type
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function getDatabaseType()
    {
        return 'sqlite3';
    }
}