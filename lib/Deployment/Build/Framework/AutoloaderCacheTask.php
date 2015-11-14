<?php
namespace Panthera\Deployment\Build\Framework;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Panthera\Components\Autoloader\Autoloader;
use Panthera\Components\Deployment\Task;
use Panthera\Components\Indexing\IndexService;

if (is_file(PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php'))
{
    require_once PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php';
}

/**
 * This task would index all classes, so the framework's autoloader could know which file to load on demand
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Components\Autoloader
 */
class AutoloaderCacheTask extends Task
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
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        foreach ($this->deployApp->indexService->mixedFilesStructure as $directoryName => $files)
        {
            if (strpos($directoryName, '/modules') !== false
                || strpos($directoryName, '/controllers') !== false
                || strpos($directoryName, '/Components') !== false)
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
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    public function indexDirectory($partial)
    {
        foreach ($this->deployApp->indexService->mixedFilesStructure[$partial] as $file => $meta)
        {
            $file = $this->app->getPath($file);
            $classes = IndexService::getClassesFromCode(file_get_contents($file));
            $this->output('Processing "' . $file . '"');

            foreach ($classes as $class)
            {
                // don't index files with a valid structure
                if (Autoloader::getForNamespace($class))
                {
                    continue;
                }

                $this->output("\t" . $class);
                $this->indexedClasses[$class] = str_replace('//', '/', str_replace(PANTHERA_FRAMEWORK_PATH, '$LIB$', $file));
                $this->indexedClasses[$class] = str_replace($this->app->appPath, '$APP$', $this->indexedClasses[$class]);
            }

            $this->output("");
        }
    }
}