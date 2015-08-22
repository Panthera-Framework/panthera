<?php
namespace Panthera\deployment;

/**
 * A group task that should be executed after every project update
 *
 * @package Panthera\deployment\build\environment
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class developerTask extends task
{
    /**
     * List of dependencies this task is pulling
     *
     * @var array
     */
    public $dependencies = array(
        'build/framework/autoloaderCache',
        'build/environment/installComposer',
        'build/framework/updateComposerPackages',
        'build/environment/shellConfiguration',
        'build/database/configurePhinx',
        'tests/PHPUnitConfigure',
        'build/database/migrate',
    );
}