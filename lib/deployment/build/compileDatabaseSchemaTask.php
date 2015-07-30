<?php
namespace Panthera\deployment;
use Panthera\FileException;
use Panthera\PantheraFrameworkException;

/**
 * Build create table schemas for all database types
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class compileDatabaseSchemaTask extends task
{
    /**
     * List of database tables
     *
     * @var array
     */
    protected $tables = [];

    /**
     * Schema parsers
     *
     * @var array
     */
    protected $schemaParsers = [];

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
        $this->createRequiredDirectories();
        $this->collectDatabaseSchemaParsers();
        $this->collectTables();
    }

    /**
     * Create directories used by this deployment task
     *
     * @throws FileException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|bool
     */
    protected function createRequiredDirectories()
    {
        $this->output('createRequiredDirectories: Performing a check for cache temporary directories');

        $dirs = array(
            $this->app->appPath. '/.content/cache/SQLSchemas',
            $this->app->appPath. '/.content/cache/SQLSchemas/SQLite3',
        );

        foreach ($dirs as $dir)
        {
            if (!is_writable(pathinfo($dir, PATHINFO_DIRNAME)))
            {
                throw new FileException('"' .pathinfo($dir, PATHINFO_DIRNAME). '" path is not writable', 'FS_NOT_WRITABLE_PATH');
            }

            if (!is_dir($dir))
            {
                $this->output('mkdir ' .$dir);
                mkdir($dir);
            }
        }

        return true;
    }

    /**
     * Find all database schema parsing modules
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    protected function collectDatabaseSchemaParsers()
    {
        foreach ($this->deployApp->indexService->mixedFilesStructure as $directoryName => $files)
        {
            if (strpos($directoryName, '/modules/databaseSchemaParser') !== false)
            {
                foreach ($files as $file => $meta)
                {
                    $file = pathinfo($file, PATHINFO_FILENAME);
                    $file = str_replace('.class', '', $file);

                    if ($file === 'BaseDatabaseSchemaParser')
                    {
                        continue;
                    }

                    $this->output('collectDatabaseSchemaParsers: Found "' .$file. '"');
                    $this->schemaParsers[] = $file;
                }
            }
        }
    }

    /**
     * Collect all tables schemas and create objects for them for every database type
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    protected function collectTables()
    {
        foreach ($this->deployApp->indexService->mixedFilesStructure as $directoryName => $files)
        {
            if (strpos($directoryName, '/schema/database') !== false)
            {
                foreach ($files as $file => $meta)
                {
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'yaml')
                    {
                        foreach ($this->schemaParsers as $schemaParser)
                        {
                            $this->output('collectTables: Generating schema for "' .pathinfo($file, PATHINFO_FILENAME). '" table');
                            $className = "\\Panthera\\database\\schemaParser\\" .$schemaParser;

                            /**
                             * @var \Panthera\database\schemaParser\BaseDatabaseSchemaParser $object
                             */
                            $this->tables[] = $object = new $className(pathinfo($file, PATHINFO_FILENAME));
                            $baseSchema = $object->generateInitialSchema();

                            $filePointer = fopen($this->app->appPath. '/.content/cache/SQLSchemas/SQLite3/' .pathinfo($file, PATHINFO_FILENAME). '.sql', 'w');
                            fwrite($filePointer, $baseSchema);
                            fclose($filePointer);
                        }
                    }
                }
            }
        }
    }
}