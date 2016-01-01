<?php
namespace Panthera\Deployment\Build\Release\Packaging;

use Panthera\Classes\BaseExceptions\FileException;
use Panthera\Components\Deployment\Task;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Deployment\ArgumentsCollection;

/**
 * Panthera Framework 2
 * --------------------
 * Generating PKGBUILD + Package for Arch Linux
 *
 * @package Panthera\Deployment\Build\Release
 */
class ArchLinuxTask extends Task
{
    public $tempDir = '';

    /** @var string $checksum */
    protected $checksum;

    public $shellArguments = [
        'keep'       => 'Don\'t remove source files from temporary directory',
        'vendor'     => 'Include vendor directory (dependencies)',
        'withoutgit' => 'Remove all .git directories',
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
        $this->createTemporaryDirectory();
        $this->copyFiles($arguments->get('vendor'), $arguments->get('withoutgit'));
        $this->generateInstallFile();
        $this->generatePKGBuildFile();
        $this->make();

        if (!$arguments->get('keep'))
        {
            $this->output('Cleaning up...');
            $this->cleanUp();
        }
    }

    /**
     * Run `makepkg` and move results to parent directory
     */
    protected function make()
    {
        system('cd ' . $this->tempDir . ' && makepkg');
        system('mv ' . $this->tempDir . '/*.tar.xz ' . $this->tempDir . '/../');
    }

    /**
     * Create temporary directory
     */
    protected function createTemporaryDirectory()
    {
        $this->tempDir = '/tmp/pfapp-' . md5(rand(9, 9999));

        if (!is_writable('/tmp/'))
        {
            throw new FileException('Path "/tmp/" is not writable', 'TEMP_DIR_NOT_WRITABLE');
        }

        if (!is_dir($this->tempDir))
        {
            mkdir($this->tempDir);
            mkdir($this->tempDir . '/src');
        }

        $this->output('~> Using temporary directory: ' . $this->tempDir);
    }

    /**
     * Copy project files
     *
     * @param bool $withVendor
     * @param bool $withoutGit
     */
    protected function copyFiles($withVendor = false, $withoutGit = true)
    {
        $this->output("~> cp " . $this->app->appPath . "/* " . $this->tempDir . "/src/");
        system("cp -R " . $this->app->appPath . "/* " . $this->tempDir . "/src/");
        system("rm -rf " . $this->tempDir . "/.git");

        if (!$withVendor && is_dir($this->tempDir . '/.content/vendor'))
        {
            $this->output('~> rm -rf ' . $this->tempDir . '/.content/vendor');
            system('rm -rf ' . $this->tempDir . '/.content/vendor');
        }

        if ($withoutGit)
        {
            $gitPaths = explode("\n", shell_exec('find ' . $this->tempDir . '/src -type d -name \'.git\''));

            foreach ($gitPaths as $path)
            {
                $this->output('~> rm ' . $path);

                if (realpath($path) === '/')
                {
                    continue;
                }

                system('rm -rf ' . $path);
            }
        }

        system("cd " . $this->tempDir . "/src && tar -zcvf ../sources.tar.gz .");

        // calculate checksum (do it outside of PHP to do not load the whole file into memory)
        $sha = shell_exec('sha256sum ' . $this->tempDir . '/sources.tar.gz');
        preg_match('/([A-Za-z0-9]+)\s*\//', $sha, $matches);
        $this->checksum = $matches[1];
    }

    /**
     * Clean up
     */
    protected function cleanUp()
    {
        if (realpath($this->tempDir) === '/')
        {
            return false;
        }

        system("rm -rf " . realpath($this->tempDir));
    }

    /**
     * @throws \Panthera\Classes\BaseExceptions\FileNotFoundException
     * @throws \Panthera\Classes\BaseExceptions\PantheraFrameworkException
     */
    protected function generateInstallFile()
    {
        $this->output('~> Generating ' . $this->tempDir . '/PFApplication.install');

        $variables = [
            '{$APP_NAME$}'       => $this->app->getName(),
            '{$PACKAGE_NAME$}'   => $this->app->getName(true),
            '{$INSTALL_NOTICE$}' => '',
            '{$POST_INSTALL$}'   => '',
            '{$POST_UPGRADE$}'   => '',
        ];

        $template = file_get_contents($this->app->getPath('/Schema/Configurations/Packaging/ArchLinux/PFApplication.install'));

        foreach ($variables as $var => $value)
        {
            $template = str_replace($var, $value, $template);
        }

        $filePointer = fopen($this->tempDir . '/PFApplication.install', 'w');
        fwrite($filePointer, $template);
        fclose($filePointer);
    }

    /**
     * Generate a PKGBUILD file
     */
    protected function generatePKGBuildFile()
    {
        $this->output('~> Generating ' . $this->tempDir . '/PKGBUILD');

        $version = $this->app->getVersionInformation()->getVersion();
        $version = str_replace('-', '.', $version);

        $variables = [
            '{$PACKAGE_NAME$}'      => $this->app->getName(true),
            '{$PACKAGE_VERSION$}'   => $version,
            '{$PACKAGE_RELEASE$}'   => 1,
            '{$ARCHIVE_CHECKSUM$}'  => $this->checksum,
        ];

        $template = file_get_contents($this->app->getPath('/Schema/Configurations/Packaging/ArchLinux/PKGBUILD'));

        foreach ($variables as $var => $value)
        {
            $template = str_replace($var, $value, $template);
        }

        $filePointer = fopen($this->tempDir . '/PKGBUILD', 'w');
        fwrite($filePointer, $template);
        fclose($filePointer);

        $this->output('`' . $template . '`');
    }
}