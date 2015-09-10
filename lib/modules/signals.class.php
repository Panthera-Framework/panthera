<?php
namespace Panthera;

/**
 * Standard signal-processing module for Panthera Framework 2
 *
 * @package Panthera\signals
 * @author Damian Kęska <damian@pantheraframework.org>
 */

class signals extends baseClass
{
    public $registeredSignals = [];

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

        // sort elements by priority ascending
        if ($this->registeredSignals[$signalName]['modified'])
        {
            ksort($this->registeredSignals[$signalName]['elements']);
            $this->registeredSignals[$signalName]['modified'] = false;
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
            throw new \InvalidArgumentException('Callback is not a function, but ' .json_encode($callback), 1);
        }

        if (!isset($this->registeredSignals[$signalName]))
        {
            $this->registeredSignals[$signalName] = [
                'modified' => false,
                'elements' => [],
            ];
        }

        /**
         * Execution priority
         */
        if (is_numeric($priority))
        {
            $priority = intval($priority);
        }
        else
        {
            $priority = count($this->registeredSignals[$signalName]['elements']);
        }

        while (isset($this->registeredSignals[$signalName]['elements'][$priority]))
        {
            $priority++;
        }

        $this->registeredSignals[$signalName]['modified'] = true;
        $this->registeredSignals[$signalName]['elements'][$priority] = $callback;
        return true;
    }
}