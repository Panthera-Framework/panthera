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
    public $modifiedElements = array();

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

        // create a SQL transaction
        $this->app->database->createTransaction();

        foreach ($this->modifiedElements as $key => $meta)
        {
            // raise a developer warning
            if (!isset($this->data[$key]))
            {
                $this->app->logging->output('Key present in $modifiedElements but not in $data.', 'debug');
                continue;
            }

            /**
             * Insert a new key
             */
            if ($meta['created'] === false)
            {
                $this->app->database->query('INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_section) VALUES (null, :key, :value, :section)', array(
                    'key'     => $key,
                    'value'   => $this->data[$key],
                    'section' => $meta['section'],
                ));
            }

            /**
             * Update an existing key
             */
            else
            {
                $this->app->database->query('UPDATE configuration SET configuration_value = :value WHERE configuration_key = :key', array(
                    'key'   => $key,
                    'value' => $this->data[$key],
                ));
            }
        }

        $this->app->database->commit();

        return true;
    }
}