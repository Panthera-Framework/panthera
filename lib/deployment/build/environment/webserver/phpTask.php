<?php
namespace Panthera\deployment;

/**
 * PHP based web server configuration deployment
 *
 * @package Panthera\Modules\Deployment\Environment\WebServer
 *  @author Damian Kęska <damian@pantheraframework.org>
 */
class phpTask extends \Panthera\deployment\task
{
    /**
     * Execute task
     */
    public function execute()
    {
        $this->app->config->get('webserver/php/port', 8080);
        $this->app->config->get('webserver/php/listen', 'localhost');
        $this->app->config->get('webserver/php/bootstrap', 'index.php');
        $this->app->config->save();
    }
}