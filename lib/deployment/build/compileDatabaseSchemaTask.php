<?php
namespace Panthera\deployment;

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
        $this->collectDatabaseSchemaParsers();
        $this->collectTables();
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
                            $className = "\\Panthera\\database\\schemaParser\\" .$schemaParser;
                            $this->tables[] = new $className(pathinfo($file, PATHINFO_FILENAME));
                        }
                    }
                }
            }
        }
    }
}