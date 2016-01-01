<?php
namespace Panthera\Components\Cache\Drivers;
use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Components\Cache\CacheInterface;

/**
 * Panthera Framework 2
 * Cache storage in SQLite3 database
 *
 * @package Panthera\Components\Cache
 */
class SQLite3CacheDriver extends BaseFrameworkClass implements CacheInterface
{
    /**
     * @const int typeSerialized Value of pf2_simple_cache.type that means we have serialized data in a row
     */
    const typeSerialized = 1;

    /**
     * @const int typePlain Plain data type
     */
    const typePlain      = 0;

    /**
     * @var \SQLite3|null $connection
     */
    public $connection   = null;

    /**
     * @var int $queries Pure statistics
     */
    public $queries      = 0;

    /**
     * Establish a connection, setup a table
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function setup()
    {
        $this->connection = new \SQLite3($this->app->appPath. '/.content/cache/applicationCache.sqlite3');
        $query = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pf2_simple_cache';"); $this->queries++;
        $this->connection->exec("pragma synchronous = off;");

        if ($query === false || $query === null)
        {
            throw new \Exception('Database is locked');
        }

        if (!$query->fetchArray())
        {
            $this->connection->exec('CREATE TABLE pf2_simple_cache (
              key TEXT PRIMARY KEY ASC NOT NULL,
              value TEXT NOT NULL,
              expires REAL NOT NULL,
              type INTEGER NOT NULL
            );

            CREATE INDEX pf2_simple_cache_idx ON pf2_simple_cache (key);'); $this->queries++;
        }
    }

    /**
     * Write to cache
     *
     * @param string $variable Variable name
     * @param mixed $value Value
     * @param int $expiration Expiration time in seconds (will count from now)
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function set($variable, $value, $expiration = 3600)
    {
        $expiration += time();
        list($type, $value) = $this->getDataType($value);

        /**
         * Updating an existing key
         */
        if ($this->exists($variable))
        {
            $state = $this->connection->prepare('
                UPDATE pf2_simple_cache SET value = :value, type = :type, expires = :expiration WHERE key = :key
            '); $this->queries++;

            $state->bindValue(':key', $variable, SQLITE3_TEXT);
            $state->bindValue(':value', $value, SQLITE3_TEXT);
            $state->bindValue(':expiration', $expiration, SQLITE3_INTEGER);
            $state->bindValue(':type', $type, SQLITE3_INTEGER);

        } else {
            /**
             * Inserting a new key
             */

            $state = $this->connection->prepare('
              INSERT INTO pf2_simple_cache (key, value, expires, type)
              VALUES (:key, :value, :expires, :type)'); $this->queries++;

            // bind params
            $state->bindValue(':key', $variable, SQLITE3_TEXT);
            $state->bindValue(':value', $value, SQLITE3_TEXT);
            $state->bindValue(':expires', $expiration, SQLITE3_INTEGER);
            $state->bindValue(':type', $type, SQLITE3_INTEGER);
        }

        return (bool)@$state->execute();
    }

    /**
     * Check if we want to serialize data or not
     *
     * @param mixed $value Input value
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array $type, $value
     */
    protected function getDataType($value)
    {
        if (in_array(gettype($value), ['string', 'float', 'double', 'integer', 'boolean', 'null']))
        {
            $type = self::typePlain;
        } else {
            $type = self::typeSerialized;
            $value = serialize($value);
        }

        return array(
            $type, $value,
        );
    }

    /**
     * Get a stored variable
     *
     * @param string $variable A variable name
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return mixed|null
     */
    public function get($variable)
    {
        $state = $this->connection->prepare('SELECT value, type, expires FROM `pf2_simple_cache` WHERE `key` = :key'); $this->queries++;
        $state->bindValue(':key', $variable, SQLITE3_TEXT);
        $data = $state->execute()->fetchArray(SQLITE3_ASSOC);

        // returns null on non-existing key
        if (!$data)
        {
            return null;

        } elseif ($data['expires'] <= time()) {
            $this->delete($variable);
            return null;
        }

        if ($data['type'] === self::typeSerialized)
        {
            $data['value'] = unserialize($data['value']);
        }

        return $data['value'];
    }

    /**
     * Remove a key from cache
     *
     * @param string $variable Key name
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function delete($variable)
    {
        $statement = $this->connection->prepare('DELETE FROM `pf2_simple_cache` WHERE key = :key'); $this->queries++;
        $statement->bindValue(':key', $variable, SQLITE3_TEXT);

        return (bool)@$statement->execute();
    }

    /**
     * Check if key exists
     *
     * @param $variable
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function exists($variable)
    {
        return ($this->get($variable) !== null);
    }

    /**
     * Clear all entries older than $maxLifeTime seconds, or just drop all entries
     *
     * @param int $maxLifeTime
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function clear($maxLifeTime = 0)
    {
        if ($maxLifeTime > 0)
        {
            $this->connection->exec('DELETE FROM `pf2_simple_cache` WHERE (' .time(). ' - ' .intval($maxLifeTime). ') >= expires');
            return true;
        }

        $this->connection->exec('DELETE FROM `pf2_simple_cache`');
        return true;
    }
}