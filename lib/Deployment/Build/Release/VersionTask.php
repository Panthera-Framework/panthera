<?php
namespace Panthera\Deployment\Build\Release;

use Panthera\Binaries\DeploymentApplication;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Deployment\ArgumentsCollection;
use Panthera\Components\Deployment\Task;
use Panthera\Components\Versioning\Version;
use Symfony\Component\Yaml\Yaml;

/**
 * PantheraFramework 2
 * -------------------
 * Visioning task allows to set version, maturity and execute
 * dependent tasks like setting version in Debian or Arch Linux packages
 *
 * @package Panthera\Deployment\Build\Release
 */
class VersionTask extends Task
{
    /** @var bool $developer */
    public $developer = false;

    /** @var bool $updateComposer */
    public $updateComposer = false;

    /** @var Version $versionInformation */
    public $versionInformation;

    /** @var array $shellArguments */
    public $shellArguments = array(
        'version'  => 'Manually set version',
        'maturity' => 'Application\'s maturity: stable|dev|testing|rc',
        'update'   => 'Automatically update version from saved template',
        'dump'     => 'Dump configuration',
        'framework'=> 'Change context to Panthera Framework 2 (for framework developers)',
        'composer' => 'Update composer.json file with new version',
    );

    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @return bool
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        if ($arguments->get('framework'))
        {
            $this->versionInformation = new Version(true);
        }
        else
        {
            $this->versionInformation = $deployment->app->getVersionInformation();
        }

        $this->developer = (bool)$arguments->get('framework');

        // composer support
        if ((bool)$arguments->get('composer'))
        {
            $this->updateComposer = $this->developer ? $deployment->app->libPath . '/composer.json' : $this->app->appPath . '/composer.json';
        }

        if ($arguments->get('update'))
        {
            return $this->update();
        }

        elseif ($arguments->get('dump'))
        {
            $this->output($this->versionInformation->dump());
            return true;
        }

        elseif ($arguments->get('version'))
        {
            $this->setVersion($arguments->get('version'), $arguments->get('maturity'));
            return $this->saveChanges();
        }

        $this->output("No changes made to version, use --version and --maturity, see --help for more information\n\nIn --version you can use:\n %rev for commit hash\n %rev.short for short commit hash\n %commits for a commits count\n\nConfiguration path:\n " . $this->versionInformation->getConfigPath());
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
     * Save configuration to file
     *
     * @Signal Deployment.Version.Save
     */
    protected function saveChanges()
    {
        $this->output('Writing version to file');

        $this->app->signals->execute('Deployment.Version.Save', [
            'version' => $this->versionInformation,
            'task'    => $this,
        ]);

        // composer support
        if ($this->updateComposer && is_file($this->updateComposer))
        {
            $content = json_decode(file_get_contents($this->updateComposer), true);
            $content['version'] = $this->versionInformation->getVersion();

            $fp = fopen($this->updateComposer, 'w');
            fwrite($fp, json_encode($content, JSON_PRETTY_PRINT));
            fclose($fp);
        }

        return $this->versionInformation->save();
    }

    /**
     * Set application version
     *
     * @param string $version
     * @param string $maturity
     *
     * @throws InvalidArgumentException
     */
    protected function setVersion($version, $maturity = '')
    {
        $template = $version;

        if (!in_array($maturity, ['stable', 'dev', 'testing', 'rc', '']))
        {
            throw new InvalidArgumentException('Invalid value for --maturity argument', 'INVALID_ARGUMENT_MATURITY');
        }

        // git commit hash id
        if (strpos($version, '%rev') !== false || strpos($version, '%commits') !== false)
        {
            $hashId = shell_exec('cd ' . $this->getGitPath() . ' && git rev-parse HEAD');

            if (!$hashId)
            {
                throw new InvalidArgumentException('Used %rev, %commits or %rev.short in --version, but project is not in a git repository', 'NOT_IN_A_GIT_REPOSITORY');
            }

            $version = str_replace('%commits', shell_exec('cd ' . $this->getGitPath() . ' && git rev-list --count HEAD'), $version);
            $version = str_replace('%rev.short', substr($hashId, 0, 7), $version);
            $version = str_replace('%rev', $hashId, $version);
        }

        $this->versionInformation->setMaturity($maturity);
        $version = str_replace("\n", "", $version);
        $version = str_replace(" ", "", $version);

        $this->output('Setting version to ' . $version . ($this->versionInformation->getVersion() ? '-' . $this->versionInformation->getMaturity() : ''));
        $this->versionInformation->setVersion($version)->setVersionTemplate($template);
    }

    /**
     * Dynamically update version string
     *
     * @Signal Deployment.Version.Update
     * @throws InvalidArgumentException
     */
    protected function update()
    {
        $this->output('Running update...');
        $this->app->signals->execute('Deployment.Version.Update', $this);

        $this->setVersion($this->versionInformation->getVersionTemplate(), $this->versionInformation->getMaturity());
        $this->saveChanges();
    }
}