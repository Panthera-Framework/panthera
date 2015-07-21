<?php
namespace Panthera\deployment;
use Panthera\baseClass;

class task extends baseClass
{
    public $dependencies = array();

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
}