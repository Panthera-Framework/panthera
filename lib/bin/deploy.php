<?php
namespace Panthera\cli;
use \Panthera\framework;
use Panthera\indexService;

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

    public function __construct()
    {
        $this->indexService = new indexService;
        $this->indexService->indexFiles();

        parent::__construct();
    }

    public function cliArgumentsHelpText()
    {
        var_dump($this->indexService->mixedFilesStructure);
    }
}

framework::runShellApplication('deployment');