<?php
namespace Panthera\Packages\BasePackage\Controllers;

use Panthera\Components\Kernel\Framework;
use Panthera\Components\Controller\BaseFrameworkController;
use Panthera\Components\Controller\Response;
use Panthera\Components\Router\Router;
use Panthera\Components\Versioning\Version;

/**
 * Panthera Framework 2
 * --------------------
 * ErrorNotFoundController
 *
 * @package Panthera\Packages\BasePackage\Controllers
 */
class ErrorNotFoundController extends BaseFrameworkController
{
    /** @var string $url */
    protected $url;

    /** @var Router $router */
    protected $router;

    /**
     * @param Router $router
     * @param string $url
     */
    public function processDebugDetails(Router $router, $url)
    {
        $this->url = $url;
        $this->router = $router;
    }

    /**
     * @API
     * @return Response
     */
    public function defaultAction()
    {
        $suffix = $this->app->isDeveloperMode() ? '-debug' : '';
        $versioning = new Version(true);
        $response = new Response([
            'request' => [
                'type'      => $_SERVER['REQUEST_METHOD'],
                'url'       => $this->url,
                'referer'   => isset($_SERVER['HTTP_REFERRER']) ? $_SERVER['HTTP_REFERRER'] : '',
                'server'    => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '',
            ],

            'routes'    => $this->router->getRoutes(),
            'log'       => implode("\n", $this->app->logging->getMessages()),
            'appPath'   => $this->app->appPath,
            'pfVersion' => $versioning->getVersion(),
        ], 'Errors/404' . $suffix . '.tpl');
        $response->setCode(404);

        return $response;
    }
}