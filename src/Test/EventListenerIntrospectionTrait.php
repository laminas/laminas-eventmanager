<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\Test;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use PHPUnit\Framework\Assert;
use ReflectionProperty;

use function array_keys;
use function iterator_to_array;
use function krsort;
use function sprintf;

use const SORT_NUMERIC;

/**
 * Trait providing utility methods and assertions for use in PHPUnit test cases.
 *
 * This trait may be composed into a test case, and provides:
 *
 * - methods for introspecting events and listeners
 * - methods for asserting listeners are attached at a specific priority
 *
 * Some functionality in this trait duplicates functionality present in the
 * version 2 EventManagerInterface and/or EventManager implementation, but
 * abstracts that functionality for use in v3. As such, components or code
 * that is testing for listener registration should use the methods in this
 * trait to ensure tests are forwards-compatible between laminas-eventmanager
 * versions.
 */
trait EventListenerIntrospectionTrait
{
    /**
     * Retrieve a list of event names from an event manager.
     *
     * @return string[]
     */
    private function getEventsFromEventManager(EventManager $manager): array
    {
        $r = new ReflectionProperty($manager, 'prioritizedProvider');
        $r->setAccessible(true);
        $provider = $r->getValue($manager);

        $r = new ReflectionProperty($provider, 'events');
        $r->setAccessible(true);
        $events = $r->getValue($provider);

        return array_keys($events);
    }

    /**
     * Retrieve an interable list of listeners for an event.
     *
     * Given an event and an event manager, returns an iterator with the
     * listeners for that event, in priority order.
     *
     * If $withPriority is true, the key values will be the priority at which
     * the given listener is attached.
     *
     * Do not pass $withPriority if you want to cast the iterator to an array,
     * as many listeners will likely have the same priority, and thus casting
     * will collapse to the last added.
     *
     * @param bool $withPriority
     */
    private function getListenersForEvent(string $event, EventManager $events, bool $withPriority = false): iterable
    {
        $event = new Event($event);

        if (! $withPriority) {
            $listeners = $events->getListenersForEvent($event);
            return iterator_to_array($listeners, false);
        }

        $r = new ReflectionProperty($events, 'provider');
        $r->setAccessible(true);
        $provider = $r->getValue($events);

        $listeners = $this->traverseListeners($provider->getListenersForEventByPriority($event), true);
        return iterator_to_array($listeners);
    }

    /**
     * Assert that a given listener exists at the specified priority.
     *
     * @param callable $expectedListener
     * @param int $expectedPriority
     * @param string $event
     * @param EventManager $events
     * @param string $message Failure message to use, if any.
     */
    private function assertListenerAtPriority(
        callable $expectedListener,
        int $expectedPriority,
        string $event,
        EventManager $events,
        string $message = ''
    ): void {
        $message = $message ?: sprintf(
            'Listener not found for event "%s" and priority %d',
            $event,
            $expectedPriority
        );
        $listeners = $this->getListenersForEvent($event, $events, true);
        $found     = false;
        foreach ($listeners as $priority => $listener) {
            if ($listener === $expectedListener
                && $priority === $expectedPriority
            ) {
                $found = true;
                break;
            }
        }
        Assert::assertTrue($found, $message);
    }

    /**
     * Returns an indexed array of listeners for an event.
     *
     * Returns an indexed array of listeners for an event, in priority order.
     * Priority values will not be included; use this only for testing if
     * specific listeners are present, or for a count of listeners.
     *
     * @return callable[]
     */
    private function getArrayOfListenersForEvent(string $event, EventManager $events): iterable
    {
        return $this->getListenersForEvent($event, $events);
    }

    /**
     * Generator for traversing listeners in priority order.
     *
     * @param bool $withPriority When true, yields priority as key.
     */
    public function traverseListeners(array $queue, bool $withPriority = false): iterable
    {
        krsort($queue, SORT_NUMERIC);

        foreach ($queue as $priority => $listeners) {
            $priority = (int) $priority;
            foreach ($listeners as $listener) {
                if ($withPriority) {
                    yield $priority => $listener;
                } else {
                    yield $listener;
                }
            }
        }
    }
}
