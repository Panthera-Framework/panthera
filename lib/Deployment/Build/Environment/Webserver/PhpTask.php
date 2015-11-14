<?php
namespace Panthera\Deployment\Build\Environment\Webserver;

use Panthera\Components\Deployment\Task;

/**
 * PHP based web server configuration deployment
 *
 *  @package Panthera\Deployment\Build\Environment\Webserver
 *  @author Damian Kęska <damian@pantheraframework.org>
 */
class PhpTask extends Task
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