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
        'build/framework/autoloaderCache',
        'build/framework/signals/updateSignalsIndex',
        'build/environment/installComposer',
        'build/framework/updateComposerPackages',
        'build/environment/shellConfiguration',
        'build/database/configurePhinx',
        'tests/PHPUnitConfigure',
        'build/database/migrate',
        'build/routing/cache',
    );
}