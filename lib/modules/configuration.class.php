<?php
namespace Panthera;

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
    protected $modifiedElements = array();

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

        if ($this->get('configuration.fromDatabase', true))
        {
            $this->app->signals->attach('framework.database.connected', array($this, 'loadFromDatabase'));
        }
    }

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
     * Remove a key from configuration
     *
     * @param string $key Name
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function remove($key)
    {
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
        if ((isset($this->data[$key]) && $this->data[$key] !== $value) || !isset($this->data[$key]))
        {

                $this->modifiedElements[$key] = [
                    'removed'  => false,
                    'modified' => microtime(true),
                    'created'  => !isset($this->data[$key]),
                    'section'  => null,
                ];

        }

        $this->data[$key] = $value;
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
            $results = $database->query('SELECT * FROM configuration WHERE configuration_section is null', array());
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
                if (!isset($this->data[$row['configuration_key']]))
                {
                    $this->data[$row['configuration_key']] = $row['configuration_value'];
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

            // raise a developer warning
            if (!isset($this->data[$key]) && !isset($meta['removed']) && !$meta['removed'])
            {
                $this->app->logging->output('Key present in $modifiedElements but not in $data, also was not marked as removed', 'debug');
                continue;
            }

            /**
             * Insert a new key
             */
            if (isset($meta['created']) && $meta['created'] === true)
            {
                $this->app->database->query('INSERT INTO configuration (configuration_key, configuration_value, configuration_section) VALUES (:key, :value, :section)', array(
                    'key'     => $key,
                    'value'   => $this->data[$key],
                    'section' => $meta['section'],
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
                $this->app->database->query('UPDATE configuration SET configuration_value = :value WHERE configuration_key = :key' .$limit, array(
                    'key'   => $key,
                    'value' => $this->data[$key],
                ));
            }

            // reset state
            unset($this->modifiedElements[$key]);
        }

        $this->app->database->commit();

        return true;
    }
}