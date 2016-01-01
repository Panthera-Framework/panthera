<?php
namespace Panthera\Deployment\Build\Environment;

use Panthera\Components\Deployment\Task;

/**
 * A group task that should be executed after every project update
 *
 * @package Panthera\Deployment\Build\Environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class DeveloperTask extends Task
{
    /**
     * List of dependencies this task is pulling
     *
     * @var array
     */
    public $dependencies = array(
        'Build/Framework/AutoloaderCache',
        'Build/Framework/Signals/UpdateSignalsIndex',
        'Build/Environment/InstallComposer',
        'Build/Framework/UpdateComposerPackages',
        'Build/Environment/ShellConfiguration',
        'Build/Database/ConfigurePhinx',
        'Tests/PHPUnitConfigure',
        'Build/Database/Migrate',
        'Build/Routing/Cache',
    );
}