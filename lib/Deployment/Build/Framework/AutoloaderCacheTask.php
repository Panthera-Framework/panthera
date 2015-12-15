<?php
namespace Panthera\Deployment\Build\Framework;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Panthera\Components\Autoloader\Autoloader;
use Panthera\Components\Deployment\Task;
use Panthera\Components\Indexing\IndexService;
use Phinx\Db\Table\Index;

if (is_file(__VENDOR_PATH__ . '/autoload.php'))
{
    require_once __VENDOR_PATH__ . '/autoload.php';
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
            if (strpos($directoryName, '/Packages') !== false)
            {
                $this->indexDirectory($directoryName);
            }
        }

        ksort($this->indexedClasses);

        // index list of packages
        $this->indexPackages();

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
                // don't index files with a valid structure, except controllers in packages
                $isController = strpos($class, '\\Packages\\') !== false && strpos($class, '\\Controllers\\') !== false;

                if (!$isController && Autoloader::getForNamespace($class))
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

    public function indexPackages()
    {
        $this->output('========================');
        $this->output('Indexing packages...');
        $this->output('');

        $packages = [];

        foreach ($this->deployApp->indexService->mixedFilesStructure as $directoryName => $files)
        {
            foreach ($files as $file => $attributes)
            {
                $file = basename(strtolower($file));

                if ($file == 'package.yml' || $file == 'packae.yaml')
                {
                    $packageName = substr($directoryName, strlen('/Package/') + 1);

                    $packages[] = $packageName;
                    $this->output('Found package: ' . $packageName);
                }
            }
        }

        $this->deployApp->indexService->writeIndexFile('packages', array_unique($packages));
    }
}