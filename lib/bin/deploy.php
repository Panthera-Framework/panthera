<?php
namespace Panthera\cli;
use \Panthera\framework;

/**
 * Panthera Framework 2 Core deployment
 *
 * @package Panthera\Deployment
 * @author Damian Kęska <damian@pantheraframework.org>
 */

require __DIR__. '/../init.php';

class deploymentApplication extends application
{

}

framework::runShellApplication('deployment');