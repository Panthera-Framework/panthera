<?php
namespace Panthera\Components\Session;

/**
 * Panthera Framework 2
 * --------------------
 * Template interface for creating Session Drivers
 *
 * @package Panthera\Components\Session
 */
interface SessionDriverInterface
{
    /**
     * Get key from session
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);


    /**
     * Set key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Clear all keys from session
     *
     * @return bool
     */
    public function clear();

    /**
     * Get cookie
     *
     * @param string $key
     * @return string
     */
    public function getCookie($key);

    /**
     * Set a cookie
     *
     * @param string $key
     * @param string $value
     * @param int $expiration
     *
     * @return $this
     */
    public function setCookie($key, $value, $expiration);

    /**
     * @param string $key
     */
    public function removeCookie($key);

    /**
     * @return string|int|float
     */
    public function getSessionId();
}