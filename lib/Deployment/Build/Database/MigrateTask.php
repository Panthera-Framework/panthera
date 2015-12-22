<?php
namespace Panthera\Deployment\Build\Database;

use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Components\Deployment\Task;

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Deployment\Build\Database
 */
class MigrateTask extends Task
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
     * @var string
     */
    protected $environment = '';

    /**
     * @var array
     */
    public $shellArguments = array(
        'environment' => '(Optional) Selects environment to migrate',
    );

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
        $this->phinxBinary = __VENDOR_PATH__ . '/bin/phinx';
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

    /**
     * Migrate up
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    private function executeMigrations()
    {
        $this->output('-> Running migrations');
        $output = $this->executeCommand('migrate -vvv --no-interaction -c "' .$this->configurationPath. '"');
        print($output);
    }

    /**
     * Parse input application arguments
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function parseArguments()
    {
        $envSearch = array_search('--environment', $_SERVER['argv']);

        if ($envSearch !== false && isset($_SERVER['argv'][($envSearch + 1)]))
        {
            $this->environment = $_SERVER['argv'][($envSearch + 1)];
        }

        // validation
        if (!in_array($this->environment, ['development', 'production', 'integrationTesting']))
        {
            $this->environment = '';
        }
    }

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        $this->detectPaths();
        $this->testConfiguration();
        $this->parseArguments();
        $this->executeMigrations();
    }
}