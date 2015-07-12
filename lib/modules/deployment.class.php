<?php
namespace Panthera;

/**
 * All deployment functionality, strongly connected to cli as to provide comfortable
 *      working Panthera Framework 2 shell.
 *
 * @package Panthera
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class deployment extends baseClass
{
    public $tasksList = array();

    /**
     * Initialization of deployment module
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->indexTasks();
    }

    public function indexTasks()
    {
        $indexService = new indexService;
        var_dump($indexService->indexFiles());
    }
}