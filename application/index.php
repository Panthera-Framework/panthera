<?php
require __DIR__. '/.content/app.php';

print("Hello world");
var_dump($app->locale->get('Developer view', 'dashboard'));