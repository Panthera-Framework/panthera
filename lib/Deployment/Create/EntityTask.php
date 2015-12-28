<?php
namespace Panthera\Deployment\Create;

use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Deployment\Task;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Deployment\ArgumentsCollection;

class EntityTask extends AbstractCreate
{
    /** @var array $shellArguments */
    public $shellArguments = [
        'table' => 'Table name',
        'id'    => 'Id column name',
    ];

    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @throws InvalidArgumentException
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        if (!$this->app->database->hasTable($arguments->get('table')))
        {
            throw new InvalidArgumentException('Table "' . $arguments->get('table') . '" does not exists', 'NO_TABLE_FOUND');
        }

        if (!isset($opts[0]) || !$opts[0])
        {
            throw new InvalidArgumentException('Please specify an Entity name', 'NO_ENTITY_NAME');
        }

        // by default put everything into Classes
        if (count(explode('/', $opts[0])) === 1)
        {
            $opts[0] = 'Classes/' . $opts[0];
        }

        $path = $this->app->appPath . '/.content/' . $opts[0] . '.php';
        $classBaseName = basename($opts[0]);
        $extends = 'Panthera\Components\Orm\ORMBaseFrameworkObject';
        $idColumn = $arguments->get('id') ? $arguments->get('id') : 'id';

        if (!is_dir(dirname($path)))
        {
            mkdir(dirname($path), 0775, true);
        }

        // calculate namespace
        $parts = explode('/', $opts[0]); end($parts);
        unset($parts[key($parts)]);
        $namespace = implode('\\', $parts);

        $this->writeFile($path, $this->deployApp->app->getPath('Schema/CodeGenerator/Entity.phps'), [
            'projectName' => $this->app->getName(),
            'className'   => $classBaseName,
            'namespace'   => $this->deployApp->app->getNamespace() . '\\' . $namespace,
            'extends'     => $extends,
            'baseNameExtends' => basename(str_replace('\\', '/', $extends)),

            'tableName'   => $arguments->get('table'),
            'idColumn'    => $idColumn,
            'idProperty'  => lcfirst($classBaseName) . ucfirst($idColumn),
        ]);
    }
}