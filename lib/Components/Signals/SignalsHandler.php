<?php
namespace Panthera\Components\Signals;

use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Standard signal-processing module for Panthera Framework 2
 *
 * =============
 * DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 * Version 2, December 2004
 * Copyright (C) 2004 Sam Hocevar
 * 14 rue de Plaisance, 75014 Paris, France
 * Everyone is permitted to copy and distribute verbatim or modified
 * copies of this license document, and changing it is allowed as long
 * as the name is changed.
 * DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 * TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
 * 0. You just DO WHAT THE FUCK YOU WANT TO.
 * 
 * See: https://pl.wikipedia.org/wiki/WTFPL
 * =============
 * 
 * @package Panthera\Components\Signals
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class SignalsHandler extends BaseFrameworkClass
{
    public $registeredSignals = [];

    /**
     * Attach signals to slots basing on signalIndexing process
     * Finds all @\signal and @\slot usage in code and attaches found class methods for selected slot
     *
     * @param string $slot Slot name
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return int Number of attached signals
     */
    protected function autoload($slot)
    {
        $count = 0;

        if (isset($this->app->applicationIndex['signals'][$slot]) && $this->app->applicationIndex['signals'][$slot])
        {
            foreach ($this->app->applicationIndex['signals'][$slot] as $registered)
            {
                if (isset($registered['call']) && is_callable($registered['call']))
                {
                    $this->attach($slot, $registered['call']);
                    $count++;
                }
            }
        }

        return $count;
    }

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
        // try to autoload events registered in code, case: there are no any existing signals attached
        if (!isset($this->registeredSignals[$signalName]) || !$this->registeredSignals[$signalName]['elements'])
        {
            if ($this->autoload($signalName))
            {
                $this->registeredSignals[$signalName]['autoload'] = true;
            }
        }

        // if still no results
        if (!isset($this->registeredSignals[$signalName]) || !$this->registeredSignals[$signalName]['elements'])
        {
            return $data;
        }

        $slot = &$this->registeredSignals[$signalName];

        // autoload - case: there are existing signals attached
        if (!$slot['autoload'])
        {
            $this->autoload($signalName);
            $slot['autoload'] = true;
        }

        // sort elements by priority ascending
        if ($slot['modified'])
        {
            ksort($slot['elements']);
            $slot['modified'] = false;
        }

        foreach ($slot['elements'] as $callback)
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
                'highestPriority'  => 0,
                'autoload' => false,
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
            // put at the end
            $priority = $this->registeredSignals[$signalName]['highestPriority'] + 1;
        }

        while (isset($this->registeredSignals[$signalName]['elements'][$priority]))
        {
            $priority++;
        }

        $this->registeredSignals[$signalName]['modified'] = true;
        $this->registeredSignals[$signalName]['elements'][$priority] = $callback;

        // set the highest priority for this slot
        if ($priority > $this->registeredSignals[$signalName]['highestPriority'])
        {
            $this->registeredSignals[$signalName]['highestPriority'] = $priority;
        }

        return true;
    }
}
