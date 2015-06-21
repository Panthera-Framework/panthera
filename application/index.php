<?php
require __DIR__. '/.content/app.php';

print("Hello world");
var_dump($app->locale->get('Developer view', 'dashboard'));

$app->cache->set('test', array(time(), 'serialized type test'), 5);
var_dump($app->cache->get('test'));
var_dump($app->cache->queries);