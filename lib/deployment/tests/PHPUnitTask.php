<?php
namespace Panthera\deployment;

class PHPUnitTask extends task
{
    /**
     * Execute external unit testing command
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
        system("phpunit --configuration " .$configurationPath);
    }
}