<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\SharedEventManagerInterface;

class StaticEventsMock implements SharedEventManagerInterface
{
    /**
     * @param non-empty-string $id,
     * @param null|string|EventInterface $event
     * @return callable[]
     */
    public function getListeners($id, $event = null)
    {
        return [];
    }

    /**
     * Attach a listener to an event
     *
     * @param  string|array $identifier Identifier(s) for event emitting component(s)
     * @param  string $event
     * @param  callable $callback PHP Callback
     * @param  int $priority Priority at which listener should execute
     * @return void
     */
    public function attach($identifier, $event, callable $listener, $priority = 1)
    {
    }

    /**
     * @param null|non-empty-string $identifier
     * @param null|string $eventName
     * @return void
     */
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
     * @param  string|int $id
     * @param  null|string $event
     * @return bool
     */
    public function clearListeners($id, $event = null)
    {
        return true;
    }
}
