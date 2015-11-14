<?php
namespace Panthera\Components\Deployment;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Deployment task skeleton
 *
 * @package Panthera\deployment
 */
class Task extends BaseFrameworkClass
{
    /**
     * @var array
     */
    public $dependencies = [];

    /**
     * @var DeploymentApplication
     */
    public $deployApp = null;

    /**
     * List of shell arguments that deployment application could take, but will be passed to proper tasks
     *
     * @var array
     */
    public $shellArguments = [];

    /**
     * Skip arguments strict checking, so unknown arguments could be passed
     *
     * @var bool
     */
    public $allowUnknownArguments = false;

    /**
     * Constructor
     *
     * @param DeploymentApplication $deployApp
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct(DeploymentApplication $deployApp)
    {
        parent::__construct();
        $this->deployApp = $deployApp;
    }

    /**
     * Output a message
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @param string $message
     */
    protected function output($message)
    {
        print($message. "\n");
    }
}