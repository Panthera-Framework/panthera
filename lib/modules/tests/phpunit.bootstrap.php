<?php
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
     * Temporary database path name
     *
     * @var string
     */
    protected $temporaryDatabaseName = '';

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

        if (!isset($app))
        {
            $app = \Panthera\framework::getInstance();
        }

        $this->app = $app;
    }

    /**
     * Setup a temporary database, and reconnect using it
     *
     * @param bool $withoutMigrations Use migrations?
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function setupDatabase($withoutMigrations = false)
    {
        $databaseConfiguration = $this->app->config->get('database');

        $databaseConfiguration['name'] = 'phpunit-' .md5(microtime(true) + rand(M_PI, (M_PI * microtime(true))));
        $databaseConfiguration['type'] = 'sqlite3';
        $this->app->config->set('database', $databaseConfiguration);

        $this->app->database = Panthera\database\driver::getInstance('SQLite3');
        $this->temporaryDatabaseName = $this->app->database->getDatabasePath();

        if (!$withoutMigrations)
        {
            $command = 'cd ' .$this->app->appPath. ' && ' .PANTHERA_FRAMEWORK_PATH. '/bin/deploy  build/database/migrate';
            $this->app->logging->output("phpunit.setupDatabase: `" .$command. "`\n" .shell_exec($command));
        }

        // register a shutdown function, so the database will be removed at the end of the script
        register_shutdown_function(array($this, 'removeTemporaryDatabase'));
    }

    /**
     * Remove a temporary created database specially for a test
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function removeTemporaryDatabase()
    {
        if ($this->temporaryDatabaseName && is_file($this->temporaryDatabaseName))
        {
            unlink($this->temporaryDatabaseName);
        }
    }
}