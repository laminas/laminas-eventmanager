<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

/**
 * Provides an implementation of ListenerSubscriberInterface::detach
 */
trait ListenerSubscriberTrait
{
    /**
     * @var callable[]
     */
    private $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function detach(PrioritizedListenerAttachmentInterface $provider): void
    {
        foreach ($this->listeners as $index => $callback) {
            $provider->detach($callback);
            unset($this->listeners[$index]);
        }
    }
}
