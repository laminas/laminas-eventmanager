<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\SharedEventManagerInterface;

class StaticEventsMock implements SharedEventManagerInterface
{
    /** @inheritDoc */
    public function getListeners($identifiers, $eventName = null)
    {
        return [];
    }

    /** @inheritDoc */
    public function attach($identifier, $eventName, callable $listener, $priority = 1)
    {
    }

    /** @inheritDoc */
    public function detach(callable $listener, $identifier = null, $eventName = null)
    {
    }

    /**
     * Retrieve all registered events for a given resource
     *
     * @param  string|int $id
     * @return array
     */
    public function getEvents($id)
    {
        return [];
    }

    /**
     * Clear all listeners for a given identifier, optionally for a specific event
     *
     * @param  string|int  $identifier
     * @param  null|string $eventName
     *
     * @return bool
     */
    public function clearListeners($identifier, $eventName = null)
    {
        return true;
    }
}
