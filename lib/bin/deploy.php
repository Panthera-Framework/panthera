<?php
namespace Panthera\cli;
use \Panthera\framework;

require __DIR__. '/../init.php';

/**
 * Panthera Framework 2 Core deployment
 *
 * @package Panthera\Deployment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class deploymentApplication extends application
{

}

framework::runShellApplication('deployment');