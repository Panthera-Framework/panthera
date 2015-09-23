<?php
namespace Panthera;
use Panthera\utils\arrayUtils;

/**
 * Panthera Framework 2 Base Configuration module
 *
 * @package Panthera\modules\configuration
 * @author Damian Kęska
 */
class configuration extends baseClass
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
    public $modifiedElements = [];

    /**
     * Store default config to check if value needs saving to database
     *      config taken from app.php
     *
     * @var array
     */
    public $defaultConfig;

    /**
     * Constructor
     *
     * @param array|null $data
     *
     * @attachSignal framework.database.connected
     * @author Damian Kęska <webnull.www@gmail.com>
     */
    public function __construct($data = null)
    {
        parent::__construct();

        if (is_array($data) && $data)
        {
            $this->data = $data;
            $this->defaultConfig = $data;
        }

        if ($this->get('configuration/fromDatabase', true))
        {
            $this->app->signals->attach('framework.database.connected', array($this, 'loadFromDatabase'));
        }
    }

    /**
     * Get a configuration key value, if not then return defaults
     * Accepts XPath as key name
     *
     * @param string $key Key name, or XPath
     * @param null|mixed $defaults Default value to set in case the configuration key was not defined yet
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return mixed
     */
    public function get($key, $defaults = null)
    {
        // detect if it's an xpath
        if (strpos($key, '/') !== false)
        {
            $value = arrayUtils::getByXPath($this->data, $key, null);

            if ($value === null && $defaults !== null)
            {
                $this->set($key, $defaults);
                return $defaults;
            }

            return $value;
        }

        if (!isset($this->data[$key]))
        {
            if ($defaults === null)
            {
                return null;
            }

            $this->set($key, $defaults);
        }


        return $this->data[$key];
    }

    /**
     * Remove a key from configuration
     *
     * @param string $key Name
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function remove($key)
    {
        if (strpos($key, '/') !== false)
        {
            $parts = explode('/', $key);

            // remove leaf node to get parent node
            $leafNode = $parts[count($parts) - 1];

            unset($parts[count($parts) - 1]);
            $parentNodePath = implode('/', $parts);

            $parent = $this->get($parentNodePath);

            // remove leaf node from parent node
            if ($parent !== null && is_array($parent) && isset($parent[$leafNode]))
            {
                unset($parent[$leafNode]);
                $this->set($parentNodePath, $parent);

                return true;
            }

            return false;
        }

        if (isset($this->data[$key]))
        {
            unset($this->data[$key]);
            $this->modifiedElements[$key] = [
                'removed' => microtime(true),
            ];
        }

        return !isset($this->data[$key]);
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
        $isPathModified = false;
        $isPath = false;

        // detect xpath
        if (strpos($key, '/') !== false)
        {
            if (arrayUtils::getByXPath($this->data, $key) !== $value)
            {
                $isPathModified = true;
            }

            // mark first level key as modified
            $path = $key;
            $key = explode('/', $key)[0];
            $isPath = true;
        }

        if ($isPathModified || ((isset($this->data[$key]) && $this->data[$key] !== $value) || !isset($this->data[$key])))
        {
            if (!isset($this->modifiedElements[$key]) || !$this->modifiedElements[$key]['created'])
            {
                $this->modifiedElements[$key] = [
                    'removed' => false,
                    'modified' => microtime(true),
                    'created' => !isset($this->data[$key]),
                    'section' => null,
                ];
            }
        }

        if ($isPath && $isPathModified)
        {
            $this->data = arrayUtils::setByXPath($this->data, $path, $value);
        }

        if (!$isPath)
        {
            $this->data[$key] = $value;
        }

        return true;
    }

    /**
     * Load configuration from database
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function loadFromDatabase()
    {
        try
        {
            $database = framework::getInstance()->database;
            $results = $database->query('SELECT * FROM configuration WHERE configuration_section is null', []);
        }
        catch (\Exception $e)
        {
            $this->app->logging->output('"configuration" table is missing, configuration will not be loaded from database, please run migrations to fill this gap', 'error');
            return false;
        }

        if (is_array($results) && $results)
        {
            foreach ($results as $row)
            {
                $key = $row['configuration_key'];

                if (isset($row['configuration_is_json']) && $row['configuration_is_json'])
                {
                    $row['configuration_value'] = json_decode($row['configuration_value'], true);
                }

                $this->data[$key] = $row['configuration_value'];

                // reset keys modification state
                if (isset($this->modifiedElements[$key]))
                {
                    unset($this->modifiedElements[$key]);
                }
            }
        }
        else
        {
            $this->app->logging->output('No additional configuration found in the database', 'info');
        }

        return true;
    }

    /**
     * Save modified configuration variables to database
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function save()
    {
        // execute save only if we modified any element
        if (!is_array($this->modifiedElements) || !$this->modifiedElements)
        {
            return false;
        }

        // check database connection, as we could call this function from different contexts
        if (!$this->app->database || !$this->app->database->isConnected())
        {
            throw new DatabaseException('configuration::save() requires a working database connection', 'FW_NO_DATABASE_CONNECTION');
        }

        $limit = '';

        if ($this->app->database->deleteUpdateLimitsAvailable())
        {
            $limit = ' LIMIT 1';
        }

        // create a SQL transaction
        $this->app->database->createTransaction();

        foreach ($this->modifiedElements as $key => $meta)
        {
            // do not save app.php config variables to database
            if (array_key_exists($key, $this->defaultConfig))
            {
                continue;
            }

            $isJson = false;
            $value = (isset($this->data[$key]) ? $this->data[$key] : null); // key could be deleted

            // this allow keeping multidimensional arrays in configuration
            if (is_array($value))
            {
                $value = json_encode($value);
                $isJson = true;
            }

            // raise a developer warning
            if (!isset($value) && !isset($meta['removed']) && !$meta['removed'])
            {
                $this->app->logging->output('Key present in $modifiedElements but not in $data, also was not marked as removed', 'debug');
                continue;
            }

            /**
             * Insert a new key
             */
            if (isset($meta['created']) && $meta['created'] === true)
            {
                $this->app->database->query('INSERT INTO configuration (configuration_key, configuration_value, configuration_section, configuration_is_json) VALUES (:key, :value, :section, :isJson)', array(
                    'key'     => $key,
                    'value'   => $value,
                    'section' => $meta['section'],
                    'isJson'  => intval($isJson),
                ));
            }

            /**
             * Remove a key
             */
            elseif (isset($meta['removed']) && $meta['removed'])
            {
                $this->app->database->query('DELETE FROM configuration WHERE configuration_key = :key' .$limit, array(
                    'key' => $key,
                ));
            }

            /**
             * Update an existing key
             */
            else
            {
                $this->app->database->query('UPDATE configuration SET configuration_value = :value, configuration_is_json = :isJson WHERE configuration_key = :key' .$limit, array(
                    'key'    => $key,
                    'value'  => $value,
                    'isJson' => intval($isJson),
                ));
            }

            // reset state
            unset($this->modifiedElements[$key]);
        }

        $this->app->database->commit();

        return true;
    }
}