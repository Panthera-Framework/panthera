<?php
namespace Panthera\Deployment\CodeInspection;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Components\Deployment\Task;

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class ConvertTabsToSpacesTask extends Task
{
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
        foreach ($this->deployApp->indexService->mixedFilesStructure as $dir)
        {
            foreach ($dir as $file => $state)
            {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                {
                    continue;
                }

                $this->output('~> Processing file "' .$file. '"');
                $this->convertFile($this->app->getPath($file));
            }
        }
    }

    /**
     * Convert tabs and new lines in a file
     *
     * @param string $path File path in filesystem
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function convertFile($path)
    {
        if (is_writable($path) && is_readable($path))
        {
            $contents = file_get_contents($path);
            $hash = md5($contents);
        
            $contents = str_replace("\t", '    ', $contents);
            $contents = str_replace("\r\n", "\n", $contents);
            $contents = str_replace("\r", "\n",   $contents);

            $filePointer = fopen($path, 'w');
            fwrite($filePointer, $contents);
            fclose($filePointer);
            
            if ($hash !== md5($contents))
            {
                $this->output('# ' .basename($path). ' was saved');
            }
        }
        else
        {
            $this->output('File at path "' .$path. '" is not writable, skipping conversion of this file');
        }
    }
}