<?php
namespace Panthera\Components\Templating;

/**
 * Interface for templating systems used to render a web page or cli application
 *
 * @package Panthera\Components\Templating
 */
interface TemplatingInterface
{
    /**
     * Pass variable from PHP to template
     *
     * @param array|string $key Single key name as string, or associative array of keys and values
     * @param null|mixed $value Value to set, optionally when passing an array as $key
     */
    public function assign($key, $value = null);

    /**
     * Configure template management manually
     * (template system dependent)
     *
     * @param array $array
     */
    public function setConfiguration($array);

    /**
     * Render a template from file or from string
     * And output or return
     *
     * @param string $templateFile Template file path, or a content as string
     * @param bool $toString
     * @param bool $isString
     *
     * @return string
     */
    public function display($templateFile, $toString = false, $isString = false);

    /**
     * Set include paths where to look for templates
     *
     * @param array $paths
     * @return mixed
     */
    public function setIncludePaths(array $paths);
}