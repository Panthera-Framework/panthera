<?php
namespace Panthera\deployment;
require_once PANTHERA_FRAMEWORK_PATH. '/vendor/autoload.php';

/**
 * This task would index all classes, so the framework's autoloader could know which file to load on demand
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class autoloaderCacheTask extends \Panthera\deployment\task
{
    /**
     * List of indexed classes
     *
     * @var array
     */
    protected $indexedClasses = [];

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
            if (strpos($directoryName, '/modules') !== false)
            {
                $this->indexDirectory($directoryName);
            }
        }

        ksort($this->indexedClasses);

        // save to cache file
        $this->deployApp->indexService->writeIndexFile('autoloader', $this->indexedClasses);
    }

    /**
     * Index classes for all files in selected directory
     *
     * @param string $partial Partial path from $this->deployApp->indexService->mixedFilesStructure
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    public function indexDirectory($partial)
    {
        foreach ($this->deployApp->indexService->mixedFilesStructure[$partial] as $file => $meta)
        {
            $file = $this->app->getPath($file);
            $classes = \Panthera\indexService::getClassesFromCode(file_get_contents($file));

            foreach ($classes as $class)
            {
                print("\t" .$class. "\n");
                $this->indexedClasses[$class] = str_replace('//', '/', str_replace(PANTHERA_FRAMEWORK_PATH, '$LIB$', $file));
            }
        }
    }
}