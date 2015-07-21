<?php
namespace Panthera\deployment;

/**
 * Group task for PHPUnit
 *
 * 1. Configure
 * 2. Run
 *
 * @package Panthera\deployment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class testsTask extends \Panthera\deployment\task
{
    /**
     * List of dependencies this task is pulling
     *
     * @var array
     */
    public $dependencies = array(
        'tests/PHPUnitConfigure',
        'tests/PHPUnit',
    );
}