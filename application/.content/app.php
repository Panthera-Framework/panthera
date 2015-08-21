<?php
/**
 * Configuration for application skeleton based on Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 */

$defaultConfig = [
    'database' => [
        'type' => 'sqlite3',
        'name' => 'database',

        // read-write user
        'host' => null,
        'user' => null,
        'password' => null,

        // read-only user
        'readOnlyUser' => null,
        'readOnlyPassword' => null,
    ],

    'application' => [
        'name'          => 'Empty application',
        'repository'    => 'http://localhost/application/repository',
        'repositoryKey' => 'xxx',
    ]
];

// if defined PHPUnit, initialize Panthera Framework 2 once again for test purposes
require_once __DIR__ . '/../../lib/init.php';