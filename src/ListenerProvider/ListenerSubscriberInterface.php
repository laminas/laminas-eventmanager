<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

interface ListenerSubscriberInterface
{
    /**
     * @param  int $priority Default priority at which to attach composed listeners.
     */
    public function attach(PrioritizedListenerAttachmentInterface $provider, int $priority = 1): void;

    public function detach(PrioritizedListenerAttachmentInterface $provider): void;
}
