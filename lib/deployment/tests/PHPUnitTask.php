<?php
namespace Panthera\deployment;

class PHPUnitTask extends \Panthera\deployment\task
{
    /**
     * Execute external unit testing command
     *
     * @param \Panthera\cli\deploymentApplication $deployment
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function execute()
    {
        $configurationPath = null;

        try {
            $configurationPath = $this->app->getPath('/.content/cache/phpunit.xml.dist');
        } catch (\Exception $e) { };

        try {
            $configurationPath = $this->app->getPath('/cache/phpunit.xml.dist');
        } catch (\Exception $e) { };

        if (!$configurationPath)
        {
            print("Error: phpunit.xml.dist not found in /.content/cache/phpunit.xml.dist\n");
            exit;
        }

        chdir($this->app->appPath. '/../');
        system("phpunit -c " .$configurationPath);
    }
}