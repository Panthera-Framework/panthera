<?php
require_once __DIR__. '/../framework.class.php';
require_once __DIR__. '/../databaseObjects/ORMBaseObject.class.php';

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
    public $temporaryDatabaseName = '';

    /**
     * Function initializes Panthera Framework for each test separately.
     *      Allows to use $this->app variable.
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    protected function setUp()
    {
        require __DIR__ . '/../../../application/.content/app.php';

        if (!isset($app))
        {
            $app = \Panthera\framework::getInstance();
        }

        $this->app = $app;
        $this->setupDatabase();
    }

    /**
     * Setup a temporary database, and reconnect using it
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function setupDatabase()
    {
        // generate random database file name
        $seed = md5(microtime(true) + rand(0, 99999));
        copy($this->app->appPath. '/.content/phpunit-testing.sqlite3', $this->app->appPath. '/.content/phpunit-testing-' .$seed. '.sqlite3');

        $this->temporaryDatabaseName = 'phpunit-testing-' .$seed;

        // reconfigure database connection
        $databaseConfiguration = $this->app->config->get('database');
        $databaseConfiguration['name'] = $this->temporaryDatabaseName;
        $databaseConfiguration['type'] = 'sqlite3';
        $this->app->config->set('database', $databaseConfiguration);

        // and reload
        $this->app->database = Panthera\database\driver::getInstance('SQLite3', true);
    }

    /**
     * When test result was not a success
     *
     * @param Exception $e
     * @throws Exception
     *
     * @author Damian Kęska <damian.keska@fingo.pl>
     */
    protected function onNotSuccessfulTest(Exception $e)
    {
        $this->removeTemporaryDatabase();
        parent::onNotSuccessfulTest($e);
    }

    /**
     * Remove temporary database connection after every test
     *
     * @author Damian Kęska <damian.keska@fingo.pl>
     */
    public function tearDown()
    {
        $this->removeTemporaryDatabase();
    }

    /**
     * Remove a temporary created database specially for a test
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function removeTemporaryDatabase()
    {
        if ($this->temporaryDatabaseName)
        {
            // disconnect from database
            unset($this->app->database);

            // remove the file
            $array = glob($this->app->appPath . '/.content/phpunit-testing-*.sqlite3');

            if ($array)
            {
                foreach ($array as $path)
                {
                    unlink($path);
                }
            }
        }
    }
}

class testORMModel extends \Panthera\database\ORMBaseObject
{
    protected static $__orm_Table = 'phpunit_orm_test_table';
    protected static $__orm_IdColumn = 'test_id';

    /**
     * @orm
     * @column test_id
     * @var int
     */
    public $testId          = null;
}