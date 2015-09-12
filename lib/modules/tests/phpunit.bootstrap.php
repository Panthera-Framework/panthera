<?php
require_once __DIR__. '/../framework.class.php';
require_once __DIR__. '/../databaseObjects/ORMBaseObject.class.php';

/**
 * Class PantheraFrameworkTestCase as to provide access to $app for PHPUnit tests.
 *
 * @package Panthera\tests
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
     * Is this class (TestCase) a big integration test?
     * If yes then database could be shared between tests inside it
     *
     * @var bool
     */
    public $integrationTestsForTestCase = false;

    /**
     * Will be set if any test would fail
     *
     * @var bool
     */
    public $testFailed = false;

    /**
     * Instance of this class
     *
     * @var static
     */
    protected static $instance = null;

    /**
     * Function initializes Panthera Framework for each Test or TestCase separately.
     *      Allows to use $this->app variable.
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    protected function setUp()
    {
        if (!$this->integrationTestsForTestCase || !$this->app)
        {
            require __DIR__ . '/../../../application/.content/app.php';

            if (!isset($app))
            {
                $app = \Panthera\framework::getInstance();
            }

            $this->app = $app;
            $this->setupDatabase();
            static::$instance = $this;
        }
    }

    /**
     * Setup a temporary database, and reconnect using it
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function setupDatabase()
    {
        // generate random database file name
        $seed = get_called_class(). '::' .$this->getName(false);

        if (!is_file($this->app->appPath. '/.content/phpunit-testing-' .$seed. '.sqlite3'))
        {
            copy($this->app->appPath . '/.content/phpunit-testing.sqlite3', $this->app->appPath . '/.content/phpunit-testing-' . $seed . '.sqlite3');
        }

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
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function onNotSuccessfulTest(Exception $e)
    {
        $this->testFailed = true;
        //$this->removeTemporaryDatabase();
        parent::onNotSuccessfulTest($e);
    }

    /**
     * Remove temporary database connection after every test
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function tearDown()
    {
        if (!$this->integrationTestsForTestCase && !static::$instance->testFailed)
        {
            $this->removeTemporaryDatabase();
        }
    }

    /**
     * If database is shared for all Tests in TestCase then remove database after all test
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public static function tearDownAfterClass()
    {
        if (static::$instance && static::$instance->integrationTestsForTestCase && !static::$instance->testFailed)
        {
            static::$instance->removeTemporaryDatabase();
        }
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
            $path = $this->app->appPath. '/.content/' .$this->temporaryDatabaseName. '.sqlite3';

            if (is_file($path))
            {
                unlink($path);
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

    /**
     * @orm
     * @column test_name
     * @var string
     */
    public $testName        = null;

    /**
     * @columnValidator testName
     * @return bool
     */
    protected function validateTestNameColumn()
    {
        if ($this->testName == 'fail-this-test')
        {
            return false;
        }

        return true;
    }
}

/**
 * Base class for unit testing of Panthera Framework 2 templating engine
 *
 * @package Panthera\template\tests
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class PantheraFrameworkTemplatingTestCase extends PantheraFrameworkTestCase
{
    /**
     * Setup a templating engine
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function setUp()
    {
        parent::setUp();
        require_once PANTHERA_FRAMEWORK_PATH. '/vendor/autoload.php';
        $this->app->template = new \Panthera\template;
    }
}