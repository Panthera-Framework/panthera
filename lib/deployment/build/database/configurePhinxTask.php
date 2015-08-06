<?php
namespace Panthera\deployment;
use Panthera\FileException;
use Symfony\Component\Yaml\Yaml;

require_once PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php';

/**
 * Generate configuration for Phinx migrations framework
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class configurePhinxTask extends task
{
    /**
     * PF2 to Phinx names translation
     *
     * @var array
     */
    private $adaptersMapping = [
        'sqlite3'    => 'sqlite',
        'mysql'      => 'mysql',
        'postgresql' => 'pgsql',
    ];

    /**
     * This method will be executed after task will be verified by deployment management
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        $databaseName = $this->app->config->get('database')['name'];

        if ($this->app->database->getDatabaseType() == 'sqlite3')
        {
            $databaseName = $this->app->database->getDatabasePath();
        }

        $config = [
            'default_migration_table' => '_migrations_phinxlog',
            'default_database'        => 'development',
            'paths' => [
                'migrations' => PANTHERA_FRAMEWORK_PATH. '/schema/databaseMigrations/',
                'migrations_app' => $this->app->appPath. '/.content/schema/databaseMigrations/',
            ],

            'environments' => [
                'development' => [
                    'adapter' => $this->adaptersMapping[$this->app->config->get('database')['type']],
                    'charset' => 'utf-8',
                    'name'    => $databaseName,
                    'host'    => $this->app->config->get('database')['host'],
                    'user'    => $this->app->config->get('database')['user'],
                    'password'=> $this->app->config->get('database')['password'],
                ],
            ]
        ];

        $yaml = Yaml::dump($config, 6, 4);
        $path = $this->app->appPath. '/.content/cache/phinx.yaml';

        if (!is_writable(dirname($path)))
        {
            throw new FileException('Path "' .$path. '" is not writable', 'FW_NOT_WRITABLE');
        }

        $this->output("\n'" .rtrim($yaml). "'");
        $filePointer = fopen($path, 'w');
        fwrite($filePointer, $yaml);
        fclose($filePointer);

        return true;
    }
}