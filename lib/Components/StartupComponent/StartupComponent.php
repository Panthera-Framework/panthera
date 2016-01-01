<?php
namespace Panthera\Components\StartupComponent;

use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Panthera Framework 2
 * --------------------
 * Startup Component
 *
 * @package Panthera\Components\StartupComponent
 */
class StartupComponent extends BaseFrameworkClass
{
    /**
     * Dummy function to overwrite with your content
     * This function is executed right after all framework's components are initialized
     * but before routing
     *
     * @overwrite
     */
    public function afterFrameworkSetup()
    {
        // dummy
    }

    /**
     * Dummy function executed after Router setup in RouteHandler
     *
     * @overwrite
     */
    public function afterRouterSetup()
    {
        // dummy
    }
}