<?php
namespace Panthera\Components\Router;

use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * @package Panthera\Components\Routing
 */
class RouteProvider extends BaseFrameworkClass
{
    /**
     * @throws PantheraFrameworkException
     * @return array
     */
    public function getRoutes()
    {
        if (!isset($this->app->applicationIndex['Routes']))
        {
            throw new PantheraFrameworkException('Compiled routes not found, please run "deploy build/routing/cache"', 'NO_COMPILED_ROUTES');
        }

        return $this->app->applicationIndex['Routes'];
    }
}