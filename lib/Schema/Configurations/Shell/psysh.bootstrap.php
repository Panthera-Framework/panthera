<?php
/**
 * PsySH bootstrap file
 *
 * @package Panthera\Deployment\Build\Environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */

/**
 * @var \Panthera\Components\Kernel\Framework $app
 */
$app = '';

require __DIR__. '/../../../init.php';

$app->setDebugging(true);
$app->logging->enabled = true;
$app->logging->printMessages = true;