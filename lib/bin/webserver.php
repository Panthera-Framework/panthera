#!/usr/bin/env php
<?php
namespace Panthera\cli;
use \Panthera\framework;

require __DIR__. '/../init.php';

/**
 * Setup a web server to run an application
 *
 * @package Panthera\Modules\Deployment\Environment\WebServer
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class webserverApplication extends application
{
    /**
     * List of supported web servers
     *
     * @var array
     */
    protected $webServers = [
        'nginx' => [
            'deployment' => '%PF2_PATH%/bin/deploy build/environment/webserver/nginx',
            'command'    => 'nginx -p %APP_PATH%/.content/configuration/nginx -c %APP_PATH/.content/configuration/nginx/default.conf',
        ],

        'PHP' => [
            'deployment' => '%PF2_PATH%/bin/deploy build/environment/webserver/php',
            'command'    => 'php -S localhost:8000 index.php',
        ]
    ];

    /**
     * Used webserver
     *
     * @var string
     */
    protected $server = 'PHP';

    /**
     * Constructor
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setupDefaults();
    }

    protected function setupDefaults()
    {
        if (array_key_exists($this->app->config->get('environment/webserver', 'PHP'), $this->webServers))
        {
            $this->server = $this->app->config->get('environment/webserver');
        }
    }

    /**
     * @param $value
     */
    protected function server_cliArgument($value)
    {
        if (array_key_exists($value, $this->webServers))
        {
            $this->server = $value;
        }
    }

    /**
     * Parse command arguments eg. "start", "stop" or "restart"
     *
     * @param string[] $args
     * @return null|void
     */
    public function parseOpts($args)
    {
        if (in_array('start', $args))
        {
            $this->start();
        }
    }

    /**
     * Start a webserver
     *
     * @action start
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function start()
    {
        $data = &$this->webServers[$this->server];

        print("~> Deploying a web server configuration\n");

        if ($data['deployment'])
        {
            $this->exec($data['deployment'], 'passthru', true);
        }
        else
        {
            print("-- No deployment configured for this web server\n");
        }

        if (method_exists($this, 'webServer' .$this->server))
        {
            $this->app->config->loadFromDatabase();
            $this->{'webServer' .$this->server}();
        }

        print("~> Running a web server startup command\n");
        $this->exec($data['command']);
    }

    /**
     * Server specific configuration
     */
    protected function webServerPHP()
    {
        $this->webServers['PHP']['command'] = 'php -S ' .$this->app->config->get('webserver/php/listen'). ':' .$this->app->config->get('webserver/php/port'). ' ' .$this->app->config->get('webserver/php/bootstrap');
    }
}

framework::runShellApplication('webserver');