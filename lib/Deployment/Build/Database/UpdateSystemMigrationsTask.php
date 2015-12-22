<?php
namespace Panthera\Deployment\Build\Database;

use Panthera\Classes\BaseExceptions\FileException;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Deployment\ArgumentsCollection;
use Panthera\Components\Deployment\Task;

/**
 * Generate configuration for Phinx migrations framework
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Deployment\Build\Database
 */
class UpdateSystemMigrationsTask extends Task
{
    /** @var array $shellArguments */
    public $shellArguments = [
        'yes' => 'Automatically agree to copy all migrations',
    ];

    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @throws FileException
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        $frameworkMigrations = scandir($this->app->libPath . '/Schema/DatabaseMigrations');

        if (!is_dir($this->app->appPath . '/.content/Schema/DatabaseMigrations/ignored/'))
        {
            mkdir($this->app->appPath . '/.content/Schema/DatabaseMigrations/ignored/');
        }

        foreach ($frameworkMigrations as $file)
        {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php')
            {
                continue;
            }

            if (is_file($this->app->appPath . '/.content/Schema/DatabaseMigrations/' . $file) || is_file($this->app->appPath . '/.content/Schema/DatabaseMigrations/ignored/' . $file))
            {
                $this->output('Skipping ' . $file, 'arrow');
                continue;
            }

            $this->output('Copying ' . $file, 'arrow');
            $input = 'y';

            if (!$arguments->get('yes'))
            {
                $input = $this->getInput('Are you sure? [y/n]');
            }

            if ($input === 'y' || $input === 'Y' || $input === 'YES' || $input === 'yes')
            {
                $dest = $this->app->appPath . '/.content/Schema/DatabaseMigrations/' . $file;
            }
            else
            {
                $dest = $this->app->appPath . '/.content/Schema/DatabaseMigrations/ignored/' . $file;
            }

            copy($this->app->libPath . '/Schema/DatabaseMigrations/' . $file, $dest);
        }
    }
}