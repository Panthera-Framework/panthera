<?php
namespace Panthera\deployment;

class testsTask extends \Panthera\deployment\task
{
    public $dependencies = array(
        'tests/PHPUnitConfigure',
        'tests/PHPUnit',
    );
}