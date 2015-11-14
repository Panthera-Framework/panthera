<?php
/**
 * Configuration for application skeleton based on Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 */

defined('PF2_NAMESPACE') ?: define('PF2_NAMESPACE', 'PFApplication');

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

        'charset'          => 'utf-8',

        /*
        // mysql

        'type'     => 'MySQL',
        'host'     => 'localhost',
        'name'     => 'database',
        'user'     => 'username',
        'password' => 'password',

        'mysql_buffered_queries' => 50,
        'timeout'                => 60,
        'charset'                => 'utf8',
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
