<?php
require __DIR__. '/.content/app.php';
$app->logging->enabled = true;
$app->locale->activeLanguage = 'pl';

print("Hello world");
var_dump($app->locale->get('Developer view', 'dashboard'));

$app->cache->set('test', array(time(), 'serialized type test'), 5);
var_dump($app->cache->get('test'));

/**
 * Select query - procedural
 */
$app->logging->startTimer();
var_dump($app->database->select('dbName', array('userName'), array(
	'|=|userId' => 313,
	'|=|group.groupId' => 3,
), array(
	'userName ASC',
	'userID DESC',
), array(
	'userName',
	'count(userId)',
)));

print $app->logging->output('SQL query in procedural-way done');


print("<br>");
/**
 * Select query - OOP
 */
$app->logging->startTimer();
$t = microtime(true);
$select = new \Panthera\database\select('users');
$select->what = array(
	'userName',
	'userId',
);

$select->where = array(
	'|=|userId' => 313,
	'|=|group.groupId' => 3,
);

$select->order = array(
	'userName ASC',
	'userID DESC',
);

$select->group = array(
	'userName',
	'count(userId)',
);

$select->execute();

print $app->logging->output('SQL query in objective-mode done');
var_dump(microtime(true) - $t);