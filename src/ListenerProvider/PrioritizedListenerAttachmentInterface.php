<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

interface PrioritizedListenerAttachmentInterface
{
    /**
     * @param  string $event The event type to which the listener will respond.
     * @param  callable $listener The listener itself.
     * @param  int $priority The priority at which to attach the listener. High
     *     priorities respond earlier; negative priorities respond later.
     */
    public function attach(string $event, callable $listener, int $priority = 1): void;

    /**
     * @param  callable $listener The listener to detach.
     * @param  null|string $event Which events to detach the listener from.
     *     When null, all events. If '*', this will only detach the wildcard
     *     entry for a listener, unless $force is true.
     */
    public function detach(callable $listener, ?string $event = null): void;

    /**
     * Attaches a listener as a wildcard listener (to all events).
     *
     * Analagous to:
     *
     * <code>
     * attach('*', $listener, $priority)
     * </code>
     *
     * The above will actually invoke this method instead.
     *
     * @param  callable $listener The listener to attach.
     * @param  int      $priority The priority at which to attach the listener.
     *     High priorities respond earlier; negative priorities respond later.
     */
    public function attachWildcardListener(callable $listener, int $priority = 1): void;

    /**
     * Detaches a wildcard listener.
     *
     * Analagous to:
     *
     * <code>
     * detach($listener, '*', $force)
     * </code>
     *
     * The above will actually invoke this method instead.
     *
     * @param  callable $listener The listener to detach.
     */
    public function detachWildcardListener(callable $listener): void;

    /**
     * @param  string $event The event for which to remove listeners.
     */
    public function clearListeners(string $event): void;
}
