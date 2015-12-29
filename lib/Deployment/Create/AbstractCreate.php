<?php
namespace Panthera\Deployment\Create;
use Panthera\Components\Deployment\Task;
use Symfony\Component\Yaml\Yaml;

class AbstractCreate extends Task
{
    /**
     * Write a configuration file from template
     *
     * @param string $path Destination file
     * @param string|null $templatePath Path to template to use, if null specified then $variables (third argument) will be serialized into Yaml instead
     * @param array $variables List of variables to pass to template
     */
    protected function writeFile($path, $templatePath, $variables)
    {
        if ($templatePath === null)
        {
            $template = Yaml::dump($variables);
        }
        else
        {
            $template = file_get_contents($templatePath);

            foreach ($variables as $variable => $value)
            {
                $template = str_replace('{$' . $variable . '$}', $value, $template);
            }
        }

        if (!is_dir(dirname($path)))
        {
            mkdir(dirname($path), 0755, true);
        }

        $this->output('Writing ' . $path, 'arrow');
        $filePointer = fopen($path, 'w');
        fwrite($filePointer, $template);
        fclose($filePointer);
    }
}