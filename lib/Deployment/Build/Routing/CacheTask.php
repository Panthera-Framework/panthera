<?php
namespace Panthera\Deployment\Build\Routing;

use Panthera\Components\Deployment\Task;
use Panthera\Components\Indexing\IndexService;
use Panthera\Components\Router\Router;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Symfony\Component\Yaml\Yaml;

class CacheTask extends Task
{
    /**
     * Indexed routes will be placed here
     *
     * @var array
     */
    protected $index = [];

    /**
     * @var Router $router
     */
    protected $router = null;

    /**
     * Filter indexed files by IndexService and pass to configuration parser
     * All files with name "controller.yml" and "controller.yaml" will be parsed
     *
     * @see parseControllerFile
     */
    public function execute()
    {
        $this->setupRouter();
        $structure = $this->deployApp->indexService->mixedFilesStructure;

        foreach ($structure as $directoryName => $files)
        {
            if ((strpos($directoryName, '/packages/') === 0 || strpos($directoryName, '/vendor/') === 0) && strpos($directoryName, '/controllers/') !== false)
            {
                foreach ($files as $fileName => $attributes)
                {
                    $name = strtolower(basename($fileName));

                    if ($name == 'controller.yml' || $name == 'controller.yaml')
                    {
                        $this->parseControllerFile($fileName);
                    }
                }
            }
        }

        $this->deployApp->indexService->writeIndexFile('Routes', $this->index);
    }

    /**
     * Setup router
     */
    protected function setupRouter()
    {
        $this->router = new Router();
    }

    /**
     * Parse controller.yml/controller.yaml configuration file
     * Should contain structure of an array like this:
     *      NameController:
     *          routes:
     *              - /name/[i:id]:
     *                  methods: GET|POST|PUT
     *                  priority: 320
     *
     * @param string $fileName
     * @throws PantheraFrameworkException
     * @throws FileNotFoundException
     */
    protected function parseControllerFile($fileName)
    {
        $parsed = Yaml::parse(file_get_contents($this->app->getPath($fileName)));
        $controllerName = basename(dirname($fileName));

        // fetch list of classes from Controller PHP file
        $classes = IndexService::getClassesFromCode(
            file_get_contents($this->app->getPath(dirname($fileName). '/' .$controllerName. '.php'))
        );

        $classes = array_filter($classes, function ($className) use($controllerName) { return strpos($className, $controllerName) !== false; });
        $className = $classes ? $classes[key($classes)] : null;

        if (!$className)
        {
            throw new PantheraFrameworkException('Class "' . $controllerName . '" not found', 'CONTROLLER_NOT_FOUND');
        }

        if (!is_array($parsed) || !isset($parsed[$className]))
        {
            throw new PantheraFrameworkException('Controller configuration placed at "' . $fileName . '" does not contain a valid structure - "' . $className . '" entry not found', 'CONTROLLER_NO_VALID_STRUCTURE');
        }

        // logging
        $this->output('[' .$controllerName. ']');

        if (isset($parsed[$className]['routes']))
        {
            foreach ($parsed[$className]['routes'] as $route => $attributes)
            {
                $compiled = $this->router->compileRoute($route, true);

                $this->index[$compiled['regex']] = [
                    'matches'    => $compiled['matches'],
                    'controller' => $className,
                    'original'   => $route,
                    'methods'    => $attributes['methods'],
                    'priority'   => $attributes['priority'],
                ];
                $this->output($route. ' => ' .$compiled['regex']);
            }
        }

        // empty row
        $this->output('');
    }
}