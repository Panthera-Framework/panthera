<?php
namespace Panthera\Deployment\Tests;

use Panthera\Components\Deployment\Task;

/**
 * Simply build a database for integration tests using migrations
 *
 * @package Panthera\deployment\unitTesting\PHPUnit
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class BuildTestDatabaseTask extends Task
{
    /**
     * Execute task
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function execute()
    {
        $command = 'cd ' .$this->app->appPath. ' && ' .PANTHERA_FRAMEWORK_PATH. '/bin/migrations migrate -e integrationTesting';
        $this->output($command);
        shell_exec($command);
    }
}