<?php
namespace Panthera\deployment;

/**
 * This task would index all classes, so the framework's autoloader could know which file to load on demand
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class autoloaderCacheTask extends \Panthera\deployment\task
{
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
        foreach ($this->deployApp->indexService->mixedFilesStructure as $directoryName => $files)
        {
            if (strpos($directoryName, '/modules/') !== false)
            {
                $this->indexDirectory($directoryName, $this->app->getPath($directoryName));
            }
        }
    }

    public function indexDirectory($partial, $absolutePath)
    {
        $indexedClasses = array();

        foreach ($this->deployApp->indexService->mixedFilesStructure[$partial] as $file => $meta)
        {
            $classes = \Panthera\indexService::getClassesFromCode(file_get_contents($this->app->getPath($file)));


        }
    }
}