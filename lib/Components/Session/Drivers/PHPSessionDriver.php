<?php
namespace Panthera\Components\Session\Drivers;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Session\SessionDriverInterface;

/**
 * Panthera Framework 2
 * --------------------
 * Stores user session and cookies using standard PHP mechanism
 *
 * @package Panthera\Components\Session\Drivers
 */
class PHPSessionDriver implements SessionDriverInterface
{
    /** @var string $appName */
    protected $appName;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->appName = Framework::getInstance()->getName(true);

        session_name(strtoupper($this->appName));
        session_start();

        if (!isset($_SESSION[$this->appName]))
        {
            $_SESSION[$this->appName] = [];
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($_SESSION[$this->appName][$key]) ? $_SESSION[$this->appName][$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $_SESSION[$this->appName][$key] = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $_SESSION[$this->appName] = [];
        return true;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expiration
     *
     * @return $this
     */
    public function setCookie($name, $value, $expiration = 3600)
    {
        setcookie($name, $value, time() + $expiration);
        return $this;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getCookie($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Unset cookie
     *
     * @param string $name
     * @return $this
     */
    public function removeCookie($name)
    {
        return $this->setCookie($name, '', -3600);
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->getCookie(strtoupper($this->appName));
    }
}