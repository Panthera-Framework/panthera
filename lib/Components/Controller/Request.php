<?php
namespace Panthera\Components\Controller;

/**
 * Request handler
 *
 * @package Panthera\Components\Controller
 */
class Request
{
    /** @var array $params */
    protected $params = [];

    /** @var array $get */
    protected $get = [];

    /** @var array $post */
    protected $post = [];

    /**
     * @param array $params
     * @param array $get
     * @param array $post
     */
    public function __construct($params, $get, $post)
    {
        $this->params = (array)$params;
        $this->get    = (array)$get;
        $this->post   = (array)$post;
    }

    /**
     * Querystring parameters getter
     *
     * @param string $parameter
     * @return string|int|float
     */
    public function get($parameter)
    {
        return isset($this->get[$parameter]) ? $this->get[$parameter] : null;
    }

    /**
     * Returns parameter from POST request
     *
     * @param string $parameter
     * @return string|int|float
     */
    public function post($parameter)
    {
        return isset($this->post[$parameter]) ? $this->post[$parameter] : null;
    }

    /**
     * Route query string parameters getter
     *
     * @param string $parameter
     * @return string|int|float
     */
    public function params($parameter)
    {
        return isset($this->params[$parameter]) ? $this->params[$parameter] : null;
    }
}