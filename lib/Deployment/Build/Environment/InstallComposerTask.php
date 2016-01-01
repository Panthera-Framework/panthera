<?php
namespace Panthera\Deployment\Build\Environment;
use Panthera\Components\Deployment\Task;

/**
 * Install Composer for a project locally
 *
 * @package Panthera\deployment\build\environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class InstallComposerTask extends Task
{
    /**
     * Install composer into /bin directory of application
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        // don't double the composer installation on travis-ci.org build
        if (in_array('--travisci', $_SERVER['argv']))
        {
            $this->output('~> Skipping composer installation on travis-ci.org');
            return true;
        }

        // create a /bin directory in application
        if (!is_dir($this->app->appPath. "/.content/Binaries/"))
        {
            mkdir($this->app->appPath. "/.content/Binaries/");
        }

        // install a new copy of composer if not installed yet
        if (!is_file($this->app->appPath. "/.content/Binaries/composer"))
        {
            $output = shell_exec("cd " . $this->app->appPath . "/.content/Binaries/ && curl -sS https://getcomposer.org/installer | php");
            print(str_replace("composer.phar", "composer", $output));
            rename($this->app->appPath . "/.content/Binaries/composer.phar", $this->app->appPath . "/.content/Binaries/composer");
        }
        else
        {
            // self update of existing installation of composer
            system("cd " . $this->app->appPath . "/.content/Binaries/ && php ./composer self-update");
        }

        return true;
    }
}