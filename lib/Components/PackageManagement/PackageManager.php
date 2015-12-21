<?php
namespace Panthera\Components\PackageManagement;

use Panthera\Classes\BaseExceptions\PackageManagementException;
use \Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Panthera Framework 2
 * --
 * Package Management
 *
 * @package Panthera\Components\PackageManagement
 */
class PackageManager extends BaseFrameworkClass
{
    /**
     * @throws PackageManagementException
     * @return array
     */
    public function getAvailablePackages()
    {
        if (!isset($this->app->applicationIndex['packages']) || !is_array($this->app->applicationIndex['packages']))
        {
            throw new PackageManagementException('Package scan not performed, please run "deploy Build/Framework/AutoloaderCache"', 'FW_APPLICATION_INDEX_NOT_FOUND');
        }

        return $this->app->applicationIndex['packages'];
    }

    /**
     * @System Core.Config(key="EnabledPackages")
     * @throws PackageManagementException
     * @return array
     */
    public function getEnabledPackages()
    {
        $packages = $this->getAvailablePackages();
        $enabledPackages = $this->app->config->get('enabledPackages', [ 'BasePackage' ]);

        if (!is_array($enabledPackages))
        {
            throw new PackageManagementException('"enabledPackages" configuration entry should be of array type', 'PACKAGE_MANAGEMENT_ENABLED_PACKAGES_ENTRY');
        }

        return array_filter($packages, function ($package) use ($enabledPackages) {
            return in_array($package, $enabledPackages);
        });
    }

    /**
     * Get include paths for all enabled packages
     *
     * @return array
     */
    public function getIncludePaths()
    {
        $packages = $this->getEnabledPackages();
        $paths = [];

        foreach ($packages as $package)
        {
            if (is_dir($this->app->appPath . '/.content/Packages/' . $package))
            {
                $paths[] = $this->app->appPath . '/.content/Packages/' . $package;
            }

            if (is_dir($this->app->libPath . '/Packages/' . $package . '/'))
            {
                $paths[] = $this->app->libPath . '/Packages/' . $package . '/';
            }
        }

        return $paths;
    }
}