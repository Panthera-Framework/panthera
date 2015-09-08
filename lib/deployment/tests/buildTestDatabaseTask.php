<?php
namespace Panthera\deployment;

/**
 * Simply build a database for integration tests using migrations
 *
 * @package Panthera\deployment\unitTesting\PHPUnit
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class buildTestDatabaseTask extends \Panthera\deployment\task
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