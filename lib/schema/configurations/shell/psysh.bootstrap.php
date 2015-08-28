<?php
/**
 * PsySH bootstrap file
 *
 * @package Panthera\deployment\build\environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */

/**
 * @var \Panthera\framework $app
 */
$app = '';

require __DIR__. '/../../../init.php';

$app->isDebugging = true;
$app->logging->enabled = true;
$app->logging->printMessages = true;