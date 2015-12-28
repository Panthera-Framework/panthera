<?php
namespace Panthera\Deployment\Create;
use Panthera\Components\Deployment\Task;

class AbstractCreate extends Task
{
    /**
     * @param string $path
     * @param string $templatePath
     * @param array $variables
     */
    protected function writeFile($path, $templatePath, $variables)
    {
        $template  = file_get_contents($templatePath);

        foreach ($variables as $variable => $value)
        {
            $template = str_replace('{$' . $variable . '$}', $value, $template);
        }

        $this->output('Writing ' . $path, 'arrow');
        $filePointer = fopen($path, 'w');
        fwrite($filePointer, $template);
        fclose($filePointer);
    }
}