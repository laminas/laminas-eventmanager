<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

/**
 * Abstract aggregate listener
 *
 * @deprecated since 3.4.0. This class will be removed in version 4.0.0, in
 *     favor of the ListenerProvider\AbstractListenerSubscriber. In most cases,
 *     subscribers should fully implement ListenerSubscriberInterface on their
 *     own, however.
 */
abstract class AbstractListenerAggregate implements ListenerAggregateInterface
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
