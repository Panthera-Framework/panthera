<?php
namespace Panthera\Deployment\Build\Database;

use Panthera\Classes\BaseExceptions\FileException;
use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Panthera\Components\Deployment\Task;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate configuration for Phinx migrations framework
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Deployment\Build\Database
 */
class ConfigurePhinxTask extends Task
{
    /**
     * PF2 to Phinx names translation
     *
     * @var array
     */
    private $adaptersMapping = [
        'SQLite3'    => 'sqlite',
        'MySQL'      => 'mysql',
        'postgresql' => 'pgsql',
    ];

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return bool
     */
    public function execute()
    {
        $databaseName = $this->app->config->get('database')['name'];

        if ($this->app->database->getDatabaseType() == 'sqlite3')
        {
            $databaseName = str_replace('.sqlite3', '', realpath($this->app->database->getDatabasePath()));
        }

        $config = [
            'default_migration_table' => '_migrations_phinxlog',
            'default_database'        => $this->app->config->get('Migrations/DefaultDatabase', 'development'),
            'paths' => [
                'migrations' => $this->app->appPath. '/.content/Schema/DatabaseMigrations/',
            ],

            'environments' => [
                'development' => [
                    'adapter' => $this->adaptersMapping[$this->app->config->get('database')['type']],
                    'charset' => $this->app->config->get('database')['charset'],
                    'name'    => $databaseName,
                    'host'    => $this->app->config->get('database')['host'],
                    'user'    => $this->app->config->get('database')['user'],
                    'pass'    => $this->app->config->get('database')['password'],
                ],

                'integrationTesting' => [
                    'adapter' => 'sqlite',
                    'charset' => $this->app->config->get('database')['charset'],
                    'name'    => $this->app->appPath. '/.content/phpunit-testing.sqlite3',
                    'host'    => null,
                    'user'    => null,
                    'pass'    => null,
                ],
            ]
        ];

        $yaml = Yaml::dump($config, 6, 4);
        $path = $this->app->appPath. '/.content/cache/phinx.yaml';

        if (!is_writable(dirname($path)))
        {
            throw new FileException('Path "' . $path . '" is not writable', 'FW_NOT_WRITABLE');
        }

        $this->output("\n'" .rtrim($yaml). "'");
        $filePointer = fopen($path, 'w');
        fwrite($filePointer, $yaml);
        fclose($filePointer);

        return true;
    }
}