<?php
namespace Panthera;

/**
 * Panthera Framework 2 Base Configuration module
 *
 * @package Panthera
 * @author Damian Kęska
 */
class configuration
{
    /**
     * Associative array of keys and values in mixed types
     *
     * @var array
     */
    public $data = array();


    /**
     * List of recently modified elements
     *
     * @var array
     */
    public $modifiedElements = array();

    /**
     * Get a configuration key value, if not then return defaults
     *
     * @param string $key Name
     * @param null|mixed $defaults Default value to set in case the configuration key was not defined yet
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return mixed
     */
    public function get($key, $defaults = null)
    {
        if (!isset($this->data[$key]))
        {
            $this->data[$key] = $defaults;
        }

        return $this->data[$key];
    }

    /**
     * Set a configuration key
     *
     * @param string $key Name
     * @param mixed $value Value
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return bool
     */
    public function set($key, $value)
    {
        if (isset($this->data[$key]) && $this->data[$key] !== $value)
        {
            $this->modifiedElements[$key] = microtime(true);
        }

        $this->data[$key] = $value;
        return true;
    }
}