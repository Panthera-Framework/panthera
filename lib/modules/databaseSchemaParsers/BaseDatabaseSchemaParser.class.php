<?php
namespace Panthera\database\schemaParser;

use Panthera\FileException;
use Panthera\framework;

abstract class BaseDatabaseSchemaParser
{
    /**
     * @var string
     */
    protected $schemaText = '';

    /**
     * Constructor
     *
     * @param string $schemaPath Table name or complete path to schema file
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct($schemaPath)
    {
        if (!pathinfo($schemaPath, PATHINFO_EXTENSION))
        {
            $schemaPath = framework::getInstance()->getPath('/schema/database/' .$schemaPath. '.yaml');
        }

        if (!is_readable($schemaPath))
        {
            throw new FileException('File "' .$schemaPath. '" is not readable', 'DB_SCHEMA_NOT_READABLE');
        }

        $this->schemaText = file_get_contents($schemaPath);
    }
}