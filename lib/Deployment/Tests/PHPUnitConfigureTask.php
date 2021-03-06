<?php
namespace Panthera\Deployment\Tests;

use Panthera\Components\Deployment\Task;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Classes\BaseExceptions\FileNotFoundException;

/**
 * This task is generating a configuration file for PHPUnit testing framework
 * Result of this task will be used in "tests/PHPUnit" task.
 *
 * Additional arguments:
 *   --coverage
 *   --travisci (to be used with --coverage)
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\unitTesting\PHPUnit
 */
class PHPUnitConfigureTask extends Task
{
    public $PHPUnitConfig = array(
        'backupGlobals'                 => 'false',
        'backupStaticAttributes'        => 'false',
        'colors'                        => 'true',
        'convertErrorsToExceptions'     => 'true',
        'convertNoticesToExceptions'    => 'true',
        'convertWarningsToExceptions'   => 'true',
        'processIsolation'              => 'true',
        'stopOnFailure'                 => 'false',
        'syntaxCheck'                   => 'false',
    );

    public $shellArguments = array(
        'coverage' => 'Configure a code coverage',
        'travisci' => 'TravisCI integration',
    );

    /**
     * Allow overriding phpUnit configuration using built-in configuration manager
     *
     * @config array phpUnit.configuration
     */
    public function prepareConfiguration()
    {
        $config = $this->app->config->get('phpUnit.configuration');

        if (is_array($config) && $config)
        {
            $this->PHPUnitConfig = array_merge($this->PHPUnitConfig, $config);
        }
    }

    /**
     * Add logging methods
     *
     * @param \SimpleXMLElement $xml
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return \SimpleXMLElement
     */
    public function addLogging($xml)
    {
        $logging = $xml->addChild('logging');

        if (in_array('--coverage', $_SERVER['argv']))
        {
            // coverage tests
            $log = $logging->addChild('log');
            $log->addAttribute('type', 'coverage-clover');

            if (in_array('--travisci', $_SERVER['argv']))
            {
                @mkdir($this->app->appPath. '/../lib/build/');
                @mkdir($this->app->appPath. '/../lib/build/logs/');
                $log->addAttribute('target', $this->app->appPath. '/../lib/build/logs/clover.xml');
            } else {
                $log->addAttribute('target', $this->app->appPath . '/.content/cache/clover.xml');
            }
        }

        return $xml;
    }

    /**
     * Filter test coverage files
     *
     * @param \SimpleXMLElement $xml
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return \SimpleXMLElement
     */
    public function filterCoverage($xml)
    {
        $filter = $xml->addChild('filter');

        // blacklist
        $blacklist = $filter->addChild('blacklist');

        $directoryBlacklist = $blacklist->addChild('directory', $this->app->libPath. "/tests/");
        $directoryBlacklist->addAttribute('suffix', '.php');

        $directoryBlacklist2 = $blacklist->addChild('directory', __VENDOR_PATH__);
        $directoryBlacklist2->addAttribute('suffix', '.php');

        return $xml;
    }

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        $this->prepareConfiguration();

        // generate XML code using built-in SimpleXMLElement class
        $xml = new \SimpleXMLElement('<phpunit/>');

        foreach ($this->PHPUnitConfig as $optionName => $value)
        {
            $xml->addAttribute($optionName, $value);
        }

        // add bootstrap
        $xml->addAttribute('bootstrap', $this->app->getPath('/Classes/Tests/phpunit.bootstrap.php'));

        // add logging and blacklist files
        $xml = $this->addLogging($xml);
        $xml = $this->filterCoverage($xml);

        // paths to test suites
        $testsuites = $xml->addChild('testsuites');

        $libSuite = $testsuites->addChild('testsuite');
        $libSuite->addAttribute('name', 'Panthera Framework 2');
        $libSuite->addChild('directory', PANTHERA_FRAMEWORK_PATH. '/Tests');
        $this->output('Adding ' . PANTHERA_FRAMEWORK_PATH. '/Tests');

        // add PF2 and application packages
        $this->addPackages($testsuites);

        $xml->saveXML($this->app->appPath. '/.content/cache/phpunit.xml.dist');
        return true;
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    protected function addPackages(\SimpleXMLElement $xml)
    {
        if (!is_dir($this->app->appPath . '/.content/Packages'))
        {
            mkdir($this->app->appPath . '/.content/Packages');
        }

        $appPackages = scandir($this->app->appPath . '/.content/Packages');
        $libPackages = scandir(PANTHERA_FRAMEWORK_PATH . '/Packages');

        // filter and remap arrays
        $appPackages = array_filter($appPackages, function ($value) { return $value !== '..' && $value !== '.'; });
        $appPackages = array_map(function ($value) { return $this->app->appPath . '/.content/Packages/' . $value; }, $appPackages);


        $libPackages = array_filter($libPackages, function ($value) { return $value !== '..' && $value !== '.'; });
        $libPackages = array_map(function ($value) { return PANTHERA_FRAMEWORK_PATH . '/Packages/' . $value; }, $libPackages);

        // and merge paths into a single array
        $packages = array_merge($appPackages, $libPackages);

        // add main directory also
        $packages[] = $this->app->appPath . '/.content/Tests';

        foreach ($packages as $packagePath)
        {
            if (is_dir($packagePath . '/Tests'))
            {
                $testSuite = $xml->addChild('testsuite');
                $testSuite->addAttribute('name', 'App/Package ' . basename($packagePath));
                $testSuite->addChild('directory', $packagePath);

                $this->output('Adding ' . $packagePath . '/Tests');
            }
        }
    }
}