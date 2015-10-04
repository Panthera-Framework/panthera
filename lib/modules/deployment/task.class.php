<?php
namespace Panthera\deployment;
use Panthera\BaseFrameworkClass;

/**
 * Deployment task skeleton
 *
 * @package Panthera\deployment
 */
class task extends BaseFrameworkClass
{
    /**
     * @var array
     */
    public $dependencies = [];

    /**
     * @var \Panthera\cli\deploymentApplication
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
     * @param \Panthera\cli\deploymentApplication $deployApp
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct(\Panthera\cli\deploymentApplication $deployApp)
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