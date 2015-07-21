<?php
namespace Panthera\deployment;

/**
 * This task is generating a configuration file for PHPUnit testing framework
 * Result of this task will be used in "tests/PHPUnit" task.
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
            $log->addAttribute('target', $this->app->appPath. '/.content/cache/clover.xml');
        }

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

        // add logging
        $xml = $this->addLogging($xml);

        // paths to test suites
        $testsuites = $xml->addChild('testsuites');

        foreach ($this->deployApp->indexService->libIndex as $path => $files)
        {
            if (strpos($path, '/tests/') === 0)
            {
                $libSuite = $testsuites->addChild('testsuite');
                $libSuite->addAttribute('name', 'Panthera Framework 2 / ' .basename($path));
                $libSuite->addChild('directory', PANTHERA_FRAMEWORK_PATH. '/' .$path);
            }
        }

        $xml->saveXML($this->app->appPath. '/.content/cache/phpunit.xml.dist');
        return true;
    }
}