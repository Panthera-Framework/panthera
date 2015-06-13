<?php
namespace Panthera;

/**
 * Standard signal-processing module for Panthera Framework 2
 *
 * @package Panthera
 * @author Damian Kęska <damian@pantheraframework.org>
 */

class signals
{
    public $registeredSignals = array();

    /**
     * Execute attached signals
     *
     * @param string $signalName Signal name
     * @param null|mixed $data Input data, if callback will return null then it will not be passed
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null
     */
    public function execute($signalName, $data = null)
    {
        if (!isset($this->registeredSignals[$signalName]) || !$this->registeredSignals[$signalName]['elements'])
        {
            return $data;
        }

        foreach ($this->registeredSignals[$signalName]['elements'] as $callback)
        {
            $tmpData = $callback($data);

            if ($tmpData !== null)
            {
                $data = $tmpData;
            }
        }

        return $data;
    }

    /**
     * Attach a callback function to place in code that is marked with $signalName
     * In case $callback would return null it would not affect passed data.
     *
     * @param string $signalName Signal name
     * @param callable $callback Callback function
     * @param null|int $priority Execution priority, null means highest priority in CURRENT queue
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function attach($signalName, $callback, $priority = null)
    {
        if (!is_callable($callback))
        {
            throw new \InvalidArgumentException('Callback is not a function', 1);
        }

        if (!isset($this->registeredSignals[$signalName]))
        {
            $this->registeredSignals[$signalName] = array(
                'modified' => false,
                'elements' => array(),
            );
        }

        /**
         * Execution priority
         */
        if (is_numeric($priority))
        {
            while (isset($this->registeredSignals[$signalName]['elements'][$priority]))
            {
                $priority++;
            }

        } else {
            $priority = count($this->registeredSignals[$signalName]['elements']);
        }

        $this->registeredSignals[$signalName]['modified'] = true;
        $this->registeredSignals[$signalName]['elements'][$priority] = $callback;
        return true;
    }
}