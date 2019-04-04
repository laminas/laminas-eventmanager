<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

use Laminas\EventManager\Exception\InvalidArgumentException;

class PrioritizedListenerProvider implements
    PrioritizedListenerAttachmentInterface,
    PrioritizedListenerProviderInterface
{
    /**
     * Subscribed events and their listeners
     *
     * STRUCTURE:
     * [
     *     <string name> => [
     *         <int priority> => [
     *             0 => [<callable listener>, ...]
     *         ],
     *         ...
     *     ],
     *     ...
     * ]
     *
     * NOTE:
     * This structure helps us to reuse the list of listeners
     * instead of first iterating over it and generating a new one
     * -> In result it improves performance by up to 25% even if it looks a bit strange
     *
     * @var array<string, array<int, callable[]>>
     */
    protected $events = [];

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->iterateByPriority(
            $this->getListenersForEventByPriority($event)
        );
    }

    /**
     * {@inheritDoc}
     * @param  string[] $identifiers Ignored in this implementation.
     * @throws InvalidArgumentException For non-object $event types.
     */
    public function getListenersForEventByPriority($event, array $identifiers = []): iterable
    {
        if (! is_object($event)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects the $event argument to be an object; received %s',
                __METHOD__,
                gettype($event)
            ));
        }

        $identifiers = is_callable([$event, 'getName'])
            ? [$event->getName()]
            : [];
        $identifiers = array_merge($identifiers, [get_class($event), '*']);

        $prioritizedListeners = [];
        foreach ($identifiers as $name) {
            if (! isset($this->events[$name])) {
                continue;
            }

            foreach ($this->events[$name] as $priority => $listOfListeners) {
                $prioritizedListeners[$priority] = isset($prioritizedListeners[$priority])
                    ? array_merge($prioritizedListeners[$priority], $listOfListeners[0])
                    : $listOfListeners[0];
            }
        }

        return $prioritizedListeners;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(string $event, callable $listener, int $priority = 1): callable
    {
        $this->events[$event][$priority][0][] = $listener;

        return $listener;
    }

    /**
     * {@inheritDoc}
     * @param bool $force Internal; used by attachWildcardListener to force
     *     removal of the '*' event.
     */
    public function detach(callable $listener, ?string $event = null, $force = false): void
    {
        if (null === $event || ('*' === $event && ! $force)) {
            $this->detachWildcardListener($listener);
            return;
        }

        if (! isset($this->events[$event])) {
            return;
        }

        foreach ($this->events[$event] as $priority => $listeners) {
            foreach ($listeners[0] as $index => $evaluatedListener) {
                if ($evaluatedListener !== $listener) {
                    continue;
                }

                // Found the listener; remove it.
                unset($this->events[$event][$priority][0][$index]);

                // If the queue for the given priority is empty, remove it.
                if (empty($this->events[$event][$priority][0])) {
                    unset($this->events[$event][$priority]);
                    break;
                }
            }
        }

        // If the queue for the given event is empty, remove it.
        if (empty($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attachWildcardListener(callable $listener, int $priority = 1): callable
    {
        $this->events['*'][(int) $priority][0][] = $listener;
        return $listener;
    }

    /**
     * {@inheritDoc}
     */
    public function detachWildcardListener(callable $listener): void
    {
        foreach (array_keys($this->events) as $event) {
            $this->detach($listener, $event, true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function clearListeners(string $event): void
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    private function iterateByPriority(array $prioritizedListeners): iterable
    {
        krsort($prioritizedListeners);
        foreach ($prioritizedListeners as $listeners) {
            yield from $listeners;
        }
    }
}
