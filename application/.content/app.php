<?php
/**
 * Configuration for application skeleton based on Panthera Framework 2
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 */

$defaultConfig = array(
    'database' => array(
        'type' => 'sqlite3',

        // read-write user
        'host' => null,
        'user' => null,
        'password' => null,

        // read-only user
        'readOnlyUser' => null,
        'readOnlyPassword' => null,
    ),
);

require __DIR__. '/../../lib/init.php';