<?php
namespace Panthera\Deployment\Create;

use Panthera\Binaries\DeploymentApplication;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Deployment\ArgumentsCollection;

/**
 * Panthera Framework 2
 * --------------------
 * Helps creating controllers with a correct structure
 *
 * @package Panthera\Deployment\Create
 */
class ControllerTask extends AbstractCreate
{
    /** @var array $shellArguments */
    public $shellArguments = [
        'package' => 'Package name where controller have to be created',
        'route'   => 'URL address route',
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
        if (!$opts)
        {
            throw new InvalidArgumentException('Please specify a controller name', 'NO_CONTROLLER_NAME');
        }

        if (!$arguments->get('package') || !is_dir($this->app->appPath . '/.content/Packages/' . $arguments->get('package')))
        {
            throw new InvalidArgumentException('--package parameter not specified', 'NO_PACKAGE_SPECIFIED');
        }

        $classBaseName = $opts[0] . 'Controller';
        $path = $this->app->appPath . '/.content/Packages/' . $arguments->get('package') . '/Controllers/' . $classBaseName . '/' . $classBaseName . '.php';
        $extends = 'Panthera\Components\Controller\BaseFrameworkController';
        $route = $arguments->get('route') ? $arguments->get('route') : '/' . lcfirst($opts[0]);

        // calculate namespace
        $parts = explode('/', 'Packages/' . $arguments->get('package') . '/Controllers/' . $opts[0]); end($parts);
        unset($parts[key($parts)]);
        $namespace = $this->app->getNamespace() . '\\' . implode('\\', $parts);

        // write generated controller skeleton code
        $this->writeFile($path, $this->deployApp->app->getPath('Schema/CodeGenerator/Controller.phps'), [
            'projectName' => $this->app->getName(),
            'className'   => $classBaseName,
            'namespace'   => $namespace,
            'extends'     => $extends,
            'baseNameExtends' => basename(str_replace('\\', '/', $extends)),
        ]);

        // write an example template
        $fp = fopen(dirname($path). '/../../Templates/' . $classBaseName . '.tpl', 'w');
        fwrite($fp, 'Hello ;-)');
        fclose($fp);

        // and generate controller.yml
        $this->writeFile(dirname($path) . '/controller.yml', null, [
            '\\' . $namespace . '\\' . $classBaseName => [
                'routes' => [
                    $route => [
                        'methods'  => 'GET|POST',
                        'priority' => 100,
                    ]
                ]
            ]
        ]);

        $this->output('Done, please run `deploy Build/Routing/Cache` to apply changes to routing', 'arrow');
    }
}