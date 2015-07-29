<?php
namespace Panthera\database\schemaParser;

use Panthera\FileException;
use Panthera\framework;
use Panthera\PantheraFrameworkException;

require_once PANTHERA_FRAMEWORK_PATH. '/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

abstract class BaseDatabaseSchemaParser
{
    /**
     * Data in plaintext
     *
     * @var string
     */
    protected $schemaText = '';

    /**
     * Parsed data
     *
     * @var array
     */
    protected $schema = [];

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
        $this->parse();
    }

    /**
     * Parse source schema
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function parse()
    {
        $this->schema = Yaml::parse($this->schemaText);
    }

    /**
     * Dummy parse schema to generate SQL code
     *
     * @override
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function generateInitialSchema()
    {
        throw new PantheraFrameworkException('Method "generateInitialSchema" not implemented in ' .get_called_class(). ' class', 'METHOD_NOT_FOUND');
    }
}

class SchemaParsingException extends PantheraFrameworkException {};