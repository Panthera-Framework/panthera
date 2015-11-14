<?php
namespace Panthera\Deployment\Build\Framework\Signals;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Panthera\Components\Deployment\Task;
use Panthera\Components\Indexing\SignalIndexing;

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework\indexService
 */
class UpdateSignalsIndexTask extends Task
{
    /**
     * Excluded paths from indexing
     *
     * @var array
     */
    protected $pathsExcluded = [
        '/.content/cache/',
        '/tests/',
        '/schema/databaseMigrations/',
    ];

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
        $collected = [];

        foreach ($this->deployApp->indexService->mixedFilesStructure as $dir)
        {
            foreach ($dir as $file => $state)
            {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                {
                    continue;
                }

                // exclude some paths eg. containing tests or libraries etc.
                $absolutePath = realpath($this->app->getPath($file));

                foreach ($this->pathsExcluded as $excludedPath)
                {
                    if (strpos($absolutePath, $excludedPath) !== false)
                    {
                        continue 2;
                    }
                }

                $this->output('-> Parsing ' .$absolutePath);
                $signals = SignalIndexing::loadFile($absolutePath);

                if ($signals)
                {
                    foreach ($signals as $slotName => &$slot)
                    {
                        foreach ($slot as &$signal)
                        {
                            unset($signal['phpDoc']);
                        }

                        $this->output('--> Found ' .$slotName. ' (' .count($slot). ')');
                    }

                    $collected = array_merge_recursive($collected, $signals);
                }
            }
        }

        // write collected signals to applicationIndex
        $this->deployApp->indexService->writeIndexFile('signals', $collected);

        return true;
    }
}