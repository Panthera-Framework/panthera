<?php
namespace Panthera\deployment;

class PHPUnitTask extends \Panthera\deployment\task
{
    public $dependencies = array(
        //'test/aaa',
        'tests/PHPUnit', // test - checking if it will not fall into infinite loop, passed!
    );

    /**
     * Execute external unit testing command
     *
     * @param \Panthera\cli\deploymentApplication $deployment
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function execute()
    {
        chdir($this->app->appPath. '/../');
        system("phpunit");
    }
}