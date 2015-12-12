<?php
namespace Panthera\Deployment\Tests;

use Panthera\Components\Deployment\Task;

/**
 * Group task for PHPUnit
 *
 * 1. Configure
 * 2. Run
 *
 * @package Panthera\deployment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class TestsTask extends Task
{
    /**
     * List of dependencies this task is pulling
     *
     * @var array
     */
    public $dependencies = [
        'Tests/BuildTestDatabase',
        'Tests/PHPUnitConfigure',
        'Tests/PHPUnit',
    ];
}