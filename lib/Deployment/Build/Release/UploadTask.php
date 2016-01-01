<?php
namespace Panthera\Deployment\Build\Release;

use Panthera\Classes\BaseExceptions\InvalidConfigurationException;
use Panthera\Components\Deployment\Task;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Deployment\ArgumentsCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Panthera Framework 2
 * --------------------
 * Releases project on git creating a tag
 *
 * @package Panthera\Deployment\Build\Release
 */
class UploadTask extends Task
{
    /** @var array $shellArguments */
    public $shellArguments = [
        'framework' => 'Change context to Panthera Framework 2 (for framework developers)',
    ];

    /** @var bool $developer */
    public $developer = false;

    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @throws InvalidConfigurationException
     * @return bool
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        $this->developer = (bool)$arguments->get('framework');
        $version = $this->getVersion();

        if (!$version)
        {
            throw new InvalidConfigurationException('Invalid version generated, it\'s empty', 'INVALID_VERSION_GENERATED');
        }

        $result = shell_exec('cd ' . $this->getGitPath() . ' && git tag -a ' . $version . ' -m "Release: ' . $version . '"');
        $this->output($result);

        if (strpos(shell_exec('cd ' . $this->getGitPath() . ' && git tag'), $version . "\n") !== false)
        {
            $this->output('~> Tag "' . $version . '" created');
            $this->output(shell_exec('cd ' . $this->getGitPath() . ' && git show "' . $version . '"'));
            return true;
        }
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        if ($this->developer)
        {
            return $this->app->frameworkPath . '/version.yml';
        }

        return $this->app->appPath . '/.content/version.yml';
    }

    /**
     * @return string
     */
    protected function getGitPath()
    {
        if ($this->developer)
        {
            return $this->app->frameworkPath;
        }

        return $this->app->appPath;
    }

    /**
     * @throws InvalidConfigurationException
     * @return string
     */
    protected function getVersion()
    {
        if (!is_file($this->getConfigPath()))
        {
            throw new InvalidConfigurationException('No version number generated, please run "Build/Release/Version"', 'NO_VERSION_GENERATED');
        }

        $data = Yaml::parse(file_get_contents($this->getConfigPath()));

        if (!isset($data['fullVersionString']))
        {
            throw new InvalidConfigurationException('Invalid version.yml format, no entry "fullVersionString" found', 'NO_VALID_VERSION_NUMBER_FOUND');
        }

        return $data['fullVersionString'];
    }
}