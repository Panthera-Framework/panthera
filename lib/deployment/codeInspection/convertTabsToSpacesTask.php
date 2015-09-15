<?php
namespace Panthera\deployment;

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class convertTabsToSpacesTask extends task
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
            $contents = str_replace("\t", '    ', $contents);
            $contents = str_replace("\r\n", "\n", $contents);
            $contents = str_replace("\r", "\n",   $contents);

            $filePointer = fopen($path, 'w');
            fwrite($filePointer, $contents);
            fclose($filePointer);
        }
        else
        {
            $this->output('File at path "' .$path. '" is not writable, skipping conversion of this file');
        }
    }
}