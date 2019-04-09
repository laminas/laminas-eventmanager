<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

/**
 * Provides logic to easily create aggregate listeners, without worrying about
 * manually detaching events
 *
 * @deprecated since 3.4.0. This trait will be removed in version 4.0.0, in
 *     favor of the ListenerProvider\ListenerSubscriberTrait. In most cases,
 *     subscribers should fully implement ListenerSubscriberInterface on their
 *     own, however.
 */
trait ListenerAggregateTrait
{
    /**
     * @var callable[]
     */
    protected $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            $events->detach($callback);
            unset($this->listeners[$index]);
        }
    }
}
