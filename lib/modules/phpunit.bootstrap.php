<?php

class PantheraFrameworkTestCase extends PHPUnit_Framework_TestCase
{
    public $app = null;

    public function setup()
    {
        require __DIR__. "/../init.php";

        $this->app = $app;
    }
}