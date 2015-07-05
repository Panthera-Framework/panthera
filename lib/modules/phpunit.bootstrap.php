<?php

class PantheraFrameworkTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Panthera\framework
     */
    public $app = null;

    public function setup()
    {
        require __DIR__. "/../../application/.content/app.php";
        $this->app = $app;
    }
}