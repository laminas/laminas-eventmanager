<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

use Psr\EventDispatcher\ListenerProviderInterface;

interface PrioritizedListenerProviderInterface extends ListenerProviderInterface
{
    /**
     * @param object $event The event for which to retrieve listeners.
     * @param string[] $identifiers For use with shared listener providers.
     *     This argument is deprecated, and will be removed in version 4.0.
     * @return iterable<int, callable[]> Returns a hash table of priorities with
     *     the associated listeners for that priority.
     */
    public function getListenersForEventByPriority(object $event, array $identifiers = []): iterable;
}
