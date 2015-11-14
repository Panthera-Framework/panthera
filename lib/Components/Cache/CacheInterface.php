<?php
namespace Panthera\Components\Cache;
/**
 * Interface for cache handlers
 *
 * @package Panthera\Components\Cache
 * @author Damian Kęska <damian@pantheraframework.org>
 * @author Mateusz Warzyńśki <lxnmen@gmail.com>
 */
interface CacheInterface
{
    public function get($variable);
    public function set($variable, $value, $expirationTime = 60);
    public function delete($variable);
    public function exists($variable);
    public function clear($maxTime = 0);
    public function setup();
}