<?php
/**
 * Configuration for application skeleton based on Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 */

$defaultConfig = [
    'database' => [

        // sqlite
        'type' => 'SQLite3',
        'name' => 'database',

        // read-write user
        'host'     => null,
        'user'     => null,
        'password' => null,

        // read-only user
        'readOnlyUser'     => null,
        'readOnlyPassword' => null,

        /*
        // mysql

        'type'     => 'MySQL',
        'host'     => 'host',
        'name'     => 'databaseName',
        'username' => 'user',
        'password' => 'password',

        'mysql_buffered_queries' => 50,
        'timeout'                => 60
        */

    ],

    'application' => [
        'name'          => 'Empty application',
        'repository'    => 'http://localhost/application/repository',
        'repositoryKey' => 'xxx',
    ]
];

// if defined PHPUnit, initialize Panthera Framework 2 once again for test purposes
require_once __DIR__ . '/../../lib/init.php';
