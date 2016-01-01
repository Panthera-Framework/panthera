<?php
namespace Panthera\Deployment\Create;

use Panthera\Components\Deployment\Task;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Deployment\ArgumentsCollection;

/**
 * Panthera Framework 2
 * --------------------
 * Class ClassTask
 *
 * @package Panthera\Deployment\Create
 */
class ClassTask extends AbstractCreate
{
    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @return bool
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        foreach ($opts as $className)
        {
            $this->createClass($className);
        }

        return true;
    }

    /**
     * @param $className
     */
    protected function createClass($className)
    {
        $path  = $this->deployApp->app->appPath . '/.content/Classes/';
        $parts = explode('/', $className);

        // if placed inside a subdirectory
        if (count($parts) > 1)
        {
            $t = $parts;
            end($t);
            unset($t[key($t)]);

            $path .= implode('/', $t) . '/';

            if (!is_dir($path))
            {
                $this->output('mkdir -p ' . $path, 'arrow');
                mkdir($path, $this->app->config->get('Deployment/Create/Class/Permissions', 0755), true);
            }
        }

        $classBaseName = basename($className);
        $classPath     = substr($className, 0, ((strlen($classBaseName) + 1) * (-1)));

        // parent class
        $extends = $this->app->config->get('Deployment/Create/Class/Extends', 'Panthera\\Components\\Kernel\\BaseFrameworkClass');

        $this->writeFile($path . '/' . $classBaseName . '.php', $this->deployApp->app->getPath('Schema/CodeGenerator/Class.phps'), [
            'projectName' => $this->app->getName(),
            'className'   => $classBaseName,
            'namespace'   => $this->deployApp->app->getNamespace() . '\\Classes\\' . str_replace('/', '\\', $classPath),
            'extends'     => $extends,
            'baseNameExtends' => basename(str_replace('\\', '/', $extends)),
        ]);
    }
}