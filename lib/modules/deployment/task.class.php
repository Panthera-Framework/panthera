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