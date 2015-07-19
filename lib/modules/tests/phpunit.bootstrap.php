<?php

// allow app.php to recognise tests and execute init.php what makes a new instance of Panthera Framework 2
define('PHPUNIT', true);

/**
 * Class PantheraFrameworkTestCase as to provide access to $app for PHPUnit tests.
 *
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class PantheraFrameworkTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Panthera Framework 2 instance.
     *
     * @var \Panthera\framework
     */
    public $app = null;

    /**
     * Function initializes Panthera Framework for each test separately.
     *      Allows to use $this->app variable.
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function setup()
    {
        require __DIR__ . '/../../../application/.content/app.php';
        $this->app = $app;
    }
}