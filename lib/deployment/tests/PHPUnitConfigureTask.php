<?php
namespace Panthera\deployment;

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
class PHPUnitConfigureTask extends \Panthera\deployment\task
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

        $directoryBlacklist2 = $blacklist->addChild('directory', $this->app->libPath. "/vendor/");
        $directoryBlacklist2->addAttribute('suffix', '.php');

        // whitelist
        $whitelist = $filter->addChild('whitelist');
        $whitelist->addAttribute('processUncoveredFilesFromWhitelist', 'true');

        $directoryWhitelist = $whitelist->addChild('directory', $this->app->libPath. '/');
        $directoryWhitelist->addAttribute('suffix', '.php');

        return $xml;
    }

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
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
        $xml->addAttribute('bootstrap', $this->app->getPath('/modules/tests/phpunit.bootstrap.php'));

        // add logging and blacklist files
        $xml = $this->addLogging($xml);
        $xml = $this->filterCoverage($xml);

        // paths to test suites
        $testsuites = $xml->addChild('testsuites');

        $libSuite = $testsuites->addChild('testsuite');
        $libSuite->addAttribute('name', 'Panthera Framework 2');
        $libSuite->addChild('directory', PANTHERA_FRAMEWORK_PATH. '/tests');

        $xml->saveXML($this->app->appPath. '/.content/cache/phpunit.xml.dist');
        return true;
    }
}