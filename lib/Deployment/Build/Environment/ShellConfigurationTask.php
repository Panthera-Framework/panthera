<?php
namespace Panthera\Deployment\Build\Environment;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Components\Deployment\Task;

/**
 * Create a Bash/ZSH configuration file that could be used to configure a shell session
 * to use handy commands to manage your project
 *
 * @package Panthera\Deployment\Build\Environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class ShellConfigurationTask extends Task
{
    /**
     * Template file content
     *
     * @var string
     */
    public $template = '';

    /**
     * Path where to save file ready to launch that will open a shell
     *
     * @var string
     */
    public $shellBinFile = '';

    public $shellArguments = array(
        'run' => 'Run shell immediately after deployment of a configuration',
    );

    /**
     * Creates a /bin directory inside of application's data directory
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function createBinDirectory()
    {
        $binDir = pathinfo($this->shellBinFile, PATHINFO_DIRNAME);

        if (!is_dir($binDir))
        {
            mkdir($binDir);
        }
    }

    /**
     * Write generated content to file
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function write()
    {
        $this->createBinDirectory();

        $this->output("Writing shell configuration file to " . $this->shellBinFile);
        $filePointer = fopen($this->shellBinFile, 'w');
        fwrite($filePointer, $this->template);
        fclose($filePointer);

        $this->output("# chmod +x " . $this->shellBinFile);
        system("chmod +x " .$this->shellBinFile);
        $this->output("Please type \"" . $this->shellBinFile . "\" in a shell to start a new session for your project");

        // automatically run the generated file if "--run" shell argument was specified
        if (in_array('--run', $_SERVER['argv']))
        {
            system($this->shellBinFile);
        }

        return is_file($this->shellBinFile) && (file_get_contents($this->shellBinFile) == $this->template);
    }

    /**
     * Generate a configuration file
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @return bool
     */
    public function execute()
    {
        $this->shellBinFile = $this->app->appPath. '/.content/Binaries/shell';

        $variables = [
            '{$SH$}'             => 'bash',
            '{$APP_PATH$}'       => $this->app->appPath,
            '{$FRAMEWORK_PATH$}' => PANTHERA_FRAMEWORK_PATH,
            '{$PROJECT_NAME$}'   => $this->app->getName(),
            '{$VENDOR_PATH$}'    => __VENDOR_PATH__,
            '{$PSYSH_BOOTSTRAP$}'=> $this->app->getPath('/Schema/Configurations/Shell/psysh.bootstrap.php'),
        ];

        $this->template = file_get_contents($this->app->getPath('/Schema/Configurations/Shell/sh-template.sh'));

        foreach ($variables as $var => $value)
        {
            $this->template = str_replace($var, $value, $this->template);
        }

        return $this->write();
    }
}