<?php
namespace Panthera\Deployment\Build\Release;

use Panthera\Binaries\DeploymentApplication;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Deployment\ArgumentsCollection;
use Panthera\Components\Deployment\Task;
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
    /** @var string $versionTemplate */
    public $versionTemplate = '0.1.%commits-%rev.short';

    /** @var string $version */
    public $version = '0.1';

    /** @var string $maturity */
    public $maturity = 'dev';

    /** @var bool $developer */
    public $developer = false;

    /** @var array $shellArguments */
    public $shellArguments = array(
        'version'  => 'Manually set version',
        'maturity' => 'Application\'s maturity: stable|dev|testing|rc',
        'update'   => 'Automatically update version from saved template',
        'dump'     => 'Dump configuration',
        'framework'=> 'Change context to Panthera Framework 2 (for framework developers)',
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
        $this->readConfig();
        $this->developer = (bool)$arguments->get('framework');

        if ($arguments->get('update'))
        {
            return $this->update();
        }

        elseif ($arguments->get('dump'))
        {
            $this->output(file_get_contents($this->app->appPath . '/.content/version.yml'));
            return true;
        }

        elseif ($arguments->get('version'))
        {
            $this->setVersion($arguments->get('version'), $arguments->get('maturity'));
            return $this->saveChanges();
        }

        $this->output("No changes made to version, use --version and --maturity, see --help for more information\n\nIn --version you can use:\n %rev for commit hash\n %rev.short for short commit hash\n %commits for a commits count");
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
     * Read and parse configuration file
     */
    protected function readConfig()
    {
        if (!is_file($this->getConfigPath()))
        {
            $this->output('Writing empty configuration file version.yml');
            $this->saveChanges();
        }

        $data = Yaml::parse(file_get_contents($this->getConfigPath()));
        $this->version = $data['version'];
        $this->maturity = isset($data['maturity']) ? $data['maturity'] : $this->maturity;
        $this->versionTemplate = isset($data['template']) ? $data['template'] : $this->versionTemplate;
    }

    /**
     * Save configuration to file
     *
     * @Signal Deployment.Version.Save
     */
    protected function saveChanges()
    {
        $this->output('Writing to ' . $this->getConfigPath());

        $contents = Yaml::dump([
            'version' => $this->version,
            'maturity' => $this->maturity,
            'template' => $this->versionTemplate
        ]);

        $fp = @fopen($this->getConfigPath(), 'w');
        @fwrite($fp, $contents);
        @fclose($fp);

        $this->app->signals->execute('Deployment.Version.Save', [
            'version'  => $this->version,
            'maturity' => $this->maturity,
            'template' => $this->versionTemplate,
            'object'   => $this,
        ]);

        return md5($contents) === md5(file_get_contents($this->getConfigPath()));
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

        $version = str_replace("\n", "", $version);
        $version = str_replace(" ", "", $version);

        $this->output('Setting version to ' . $version . ($this->maturity ? '-' . $this->maturity : ''));
        $this->version = $version;
        $this->versionTemplate = $template;
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

        list($this->versionTemplate, $this->maturity) = $this->app->signals->execute('Deployment.Version.Update', [
            $this->versionTemplate, $this->maturity,
        ]);

        $this->setVersion($this->versionTemplate, $this->maturity);
        $this->saveChanges();
    }
}