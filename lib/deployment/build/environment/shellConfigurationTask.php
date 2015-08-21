<?php
namespace Panthera\deployment;

/**
 * Create a ZSH configuration file that could be used to configure a shell session
 * to use handy commands to manage your project
 *
 * @package Panthera\deployment\build\environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class shellConfigurationTask extends task
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

    /**
     * Write generated content to file
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function write()
    {
        $binDir = pathinfo($this->shellBinFile, PATHINFO_DIRNAME);

        if (!is_dir($binDir))
        {
            mkdir($binDir);
        }

        $filePointer = fopen($this->shellBinFile, 'w');
        fwrite($filePointer, $this->template);
        fclose($filePointer);
        system("chmod +x " .$this->shellBinFile);

        return is_file($this->shellBinFile) && (file_get_contents($this->shellBinFile) == $this->template);
    }

    /**
     * Generate a configuration file
     *
     * @throws \Panthera\FileNotFoundException
     * @throws \Panthera\PantheraFrameworkException
     *
     * @return bool
     */
    public function execute()
    {
        $this->shellBinFile = $this->app->appPath. '/.content/bin/shell';
        $variables = [
            '{$APP_PATH$}' => $this->app->appPath,
            '{$FRAMEWORK_PATH$}' => PANTHERA_FRAMEWORK_PATH,
        ];

        $this->template = file_get_contents($this->app->getPath('/schema/configurations/shell/sh-template'));

        foreach ($variables as $var => $value)
        {
            $this->template = str_replace($var, $value, $this->template);
        }

        return $this->write();
    }
}