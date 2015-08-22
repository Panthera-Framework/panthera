<?php
namespace Panthera\deployment;

/**
 * Install Composer for a project locally
 *
 * @package Panthera\deployment\build\environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class installComposerTask extends task
{
    /**
     * Install composer into /bin directory of application
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function execute()
    {
        // install a new copy of composer if not installed yet
        if (!is_file($this->app->appPath. "/.content/bin/composer"))
        {
            $output = shell_exec("cd " . $this->app->appPath . "/.content/bin/ && curl -sS https://getcomposer.org/installer | php");
            print(str_replace("composer.phar", "composer", $output));
            rename($this->app->appPath . "/.content/bin/composer.phar", $this->app->appPath . "/.content/bin/composer");
        }
        else
        {
            // self update of existing installation of composer
            system("cd " . $this->app->appPath . "/.content/bin/ && php ./composer self-update");
        }

        return true;
    }
}