<?php
namespace Panthera\Deployment\Create;

use Panthera\Binaries\DeploymentApplication;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Deployment\ArgumentsCollection;

/**
 * Panthera Framework 2
 * --------------------
 * Allows creating new packages from shell
 *
 * @package Panthera\Deployment\Create
 */
class PackageTask extends AbstractCreate
{
    /*public $shellArguments = [
        'version' => 'Sets package version',
    ];*/

    /**
     * @param DeploymentApplication $deployment
     * @param array $opts
     * @param ArgumentsCollection $arguments
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function execute(DeploymentApplication $deployment, array $opts, ArgumentsCollection $arguments)
    {
        if (!$opts)
        {
            throw new InvalidArgumentException('Package name not specified, please use `deploy Create/Package PackageName`', 'NO_PACKAGE_NAME');
        }

        $path = $this->app->appPath . '/.content/Packages/' . $opts[0];

        if (is_dir($path))
        {
            throw new InvalidArgumentException('Package already exists at your application\'s directory', 'PACKAGE_ALREADY_EXISTS');
        }

        $properties = [
            'name'          => basename($opts[0]),
            'title'         => $this->ask('Title', 'regexp@[^A-Za-z0-9\.\,\-\_\ ]'),
            'version'       => $this->ask('Version', 'regexp@[^0-9\.\_\-a-zA-Z]'),
            'author'        => $this->ask('Author name', 'regexp@[^A-Za-z0-9\.\,\-\_\ ]'),
            'authorMail'    => $this->ask('Author e-mail address', 'email'),
        ];

        $this->writeFile($path . '/package.yml', null, $properties);
        mkdir($path . '/Controllers', 0755);
        mkdir($path . '/Templates', 0755);
        mkdir($path . '/Translations', 0755);
        mkdir($path . '/Tests', 0755);

        $this->writeFile($path . '/README.md', null, "Write your short \"how to start\" here");
    }
}