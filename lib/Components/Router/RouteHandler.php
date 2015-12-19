<?php
namespace Panthera\Components\Router;

use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Router\Router;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

/**
 * @package Panthera\Components\Routing
 */
class RouteHandler extends BaseFrameworkClass
{
    /** @var Router */
    protected $router = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->router = new Router();

        $provider = new RouteProvider();
        $this->router->setRoutes(array_merge($this->router->getRoutes(), $provider->getRoutes()));
        $this->app->component->afterRouterSetup($this);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set routing root path (in case that application could be placed in a subdirectory)
     *
     * @param string $path eg. "subdirectory" for "http://domain.org/subdirectory/"
     * @return $this
     */
    public function setRootPath($path)
    {
        $this->app->config->set('Routing/rootPath', '/' . $path);
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseLink()
    {
        $path = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . ($this->app->config->get('Routing/rootPath') ? $this->app->config->get('Routing/rootPath') : '');

        if (substr($path, -1) !== '/')
        {
            $path .= '/';
        }

        return $path;
    }

    /**
     * @throws PantheraFrameworkException
     */
    public function handleRequest()
    {
        // http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        $url = $_SERVER['REQUEST_URI'];

        if ($this->app->config->get('Routing/rootPath'))
        {
            $url = '/' . substr($url, strlen($this->app->config->get('Routing/rootPath')));
        }

        if (strpos($url, '?') !== false)
        {
            $url = substr($url, 0, strpos($url, '?'));
        }

        // pass application's base URL to the template
        if ($this->app->template)
        {
            $this->app->template->assign('baseURL', $this->getBaseLink());
        }

        $routing = $this->router->resolve(
            $url
        );

        if ($routing)
        {
            $target = $routing['target'];
            $params = array_merge($_GET, $routing['params']);
        }
        else
        {
            // support for default controller - index on main path "/"
            if ($url === '/')
            {
                $target = Framework::getInstance()->getClassName('Packages\\BasePackage\\Controllers\\IndexController');
                $params = $_GET;
            }
            else
            {
                $target = isset($_GET['c']) ? $_GET['c'] . 'Controller' : 'ErrorNotFound';
                $params = $_GET;

                // get class full namespace
                $classes = \Panthera\Components\Autoloader\Autoloader::getIndexedClasses(false);

                if (isset($classes[$target]))
                {
                    $target = $classes[$target];
                }
            }
        }

        $this->debug('Target resolution: ' . $target);

        if (class_exists($target))
        {
            /** @var Panthera\Components\Controller\BaseFrameworkController $controller */
            $controllerName = $target;
            $controller = new $controllerName();
            $controller->processRequest($params, $_GET, $_POST);
            $controller->display();
        }
        else
        {
            throw new PantheraFrameworkException('No valid controller found, even ErrorNotFoundController is missing', 'CONTROLLER_NOT_EXISTS');
        }
    }
}