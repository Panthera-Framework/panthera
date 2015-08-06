<?php
namespace Panthera\deployment;
use Panthera\PantheraFrameworkException;

require_once PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php';

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class migrateTask extends task
{
    /**
     * @var null|string
     */
    protected $phinxBinary = null;

    /**
     * @var null|string
     */
    protected $configurationPath = null;

    /**
     * @var null
     */
    protected $targetPath = null;

    /**
     * Execute a command
     *
     * @param string $arguments
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    private function executeCommand($arguments)
    {
        $command = $this->phinxBinary. ' ' .$arguments;
        $this->output('$ ' .$command);

        return shell_exec($command);
    }

    /**
     * Detect Phinx paths
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    private function detectPaths()
    {
        $this->phinxBinary = PANTHERA_FRAMEWORK_PATH . '/vendor/bin/phinx';
        $this->configurationPath = $this->app->appPath. '/.content/cache/phinx.yaml';
        $this->targetPath = $this->app->appPath. '/.content/cache/';
    }

    /**
     * Check configuration using "test" command built-in Phinx
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    private function testConfiguration()
    {
        $output = $this->executeCommand('test -c "' .$this->configurationPath. '"');

        if (strpos($output, 'success!') === false)
        {
            throw new PantheraFrameworkException('Migration tool ended up with an error, here is output: ' .json_encode($output), 'FW_MIGRATE_CONFIG_ERROR');
        }

        $this->output('-> Phinx configuration test passed');
    }

    private function executeMigrations()
    {
        $this->output('-> Running migrations');
        $output = $this->executeCommand('migrate -vvv --no-interaction -c "' .$this->configurationPath. '"');

        var_dump($output);
    }

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        $this->detectPaths();
        $this->testConfiguration();
        $this->executeMigrations();
    }
}