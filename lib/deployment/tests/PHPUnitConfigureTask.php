<?php
namespace Panthera\deployment;

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

    public function execute()
    {
        $xml = new \SimpleXMLElement('<phpunit/>');

        foreach ($this->PHPUnitConfig as $optionName => $value)
        {
            $xml->addAttribute($optionName, $value);
        }

        // add bootstrap
        $xml->addAttribute('bootstrap', $this->app->getPath('/modules/tests/phpunit.bootstrap.php'));

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
    }
}