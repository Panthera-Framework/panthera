<?php
namespace Panthera\Components\Deployment;

/**
 * Panthera Framework 2
 * --------------------
 * ArgumentsCollection represents cli arguments passed to deployment task
 *
 * @package Panthera\Components\Deployment
 */
class ArgumentsCollection
{
    /**
     * @var array $args
     */
    protected $args;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * @param string $arg
     * @return string|null
     */
    public function get($arg)
    {
        return isset($this->args[$arg]) ? $this->args[$arg] : null;
    }

    /**
     * @return array
     */
    public function listAll()
    {
        return $this->args;
    }
}