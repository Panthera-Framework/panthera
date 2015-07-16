<?php
namespace Panthera\cli;
use \Panthera\framework;
use Panthera\indexService;
use Panthera\PantheraFrameworkException;

require __DIR__. '/../init.php';

/**
 * Panthera Framework 2 Core deployment
 *
 * @package Panthera\Deployment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class deploymentApplication extends application
{
    /**
     * @var null|indexService
     */
    public $indexService = null;

    /**
     * List of available modules
     *
     * @var array
     */
    public $modules = array();

    /**
     * List of all runned tasks
     *
     * @var array
     */
    public $runnedTasks = array();

    /**
     * Constructor
     * Prepare a list of deployment services
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct()
    {
        $this->indexService = new indexService;
        $this->indexService->indexFiles();

        foreach ($this->indexService->mixedFilesStructure as $folder => $files)
        {
            if (strpos($folder, '/deployment') === 0)
            {
                foreach ($files as $filePath => $value)
                {
                    $this->modules[substr(str_replace('Task.php', '', $filePath), 12)] = $filePath;
                }
            }
        }

        parent::__construct();
    }

    /**
     * List all available deployment modules
     *
     * @cli optional
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function modules_cliArgument()
    {
        print(implode("\n", array_keys($this->modules)));
        print("\n");
    }

    /**
     * Parse list of modules given from commandline and execute tasks
     *
     * @param \string[] $opts
     *
     * @throws PantheraFrameworkException
     * @throws \Panthera\FileNotFoundException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|void
     */
    public function parseOpts($opts)
    {
        foreach ($opts as $moduleName)
        {
            // don't run same task again
            if (isset($this->runnedTasks[$moduleName]))
            {
                continue;
            }

            if (!isset($this->modules[$moduleName]))
            {
                print("Error: Task \"" .$moduleName. "\" does not exists\n");
                exit;
            }

            print("=======> Running task " .$moduleName. "\n");

            require $this->app->getPath($this->modules[$moduleName]);

            $taskName = "\\Panthera\\deployment\\" .basename($moduleName). "Task";
            $this->runnedTasks[$moduleName] = new $taskName;

            if ($this->runnedTasks[$moduleName]->dependencies)
            {
                $this->parseOpts($this->runnedTasks[$moduleName]->dependencies);
            }

            if (!method_exists($this->runnedTasks[$moduleName], 'execute'))
            {
                print("Error: Method execute does not exists in \"" .$moduleName. "\", cannot start task\n");
                exit;
            }

            $this->runnedTasks[$moduleName]->execute($this);
        }
    }
}

framework::runShellApplication('deployment');