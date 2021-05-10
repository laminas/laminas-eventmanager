<?php

namespace LaminasTest\EventManager\Test;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Traversable;

use function iterator_to_array;
use function sprintf;

class EventListenerIntrospectionTraitTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    protected function setUp() : void
    {
        $this->events = new EventManager();
    }

    public function testGetEventsFromEventManagerReturnsEventList()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo', function ($e) {});
        $this->events->attach('bar', function ($e) {});
        $this->events->attach('baz', function ($e) {});
        // @codingStandardsIgnoreEnd

        self::assertEquals(['foo', 'bar', 'baz'], $this->getEventsFromEventManager($this->events));
    }

    public function testGetListenersForEventReturnsIteratorOfListenersForEventInPriorityOrder()
    {
        // @codingStandardsIgnoreStart
        $callback1 = function ($e) {};
        $callback2 = function ($e) {};
        $callback3 = function ($e) {};
        $callback4 = function ($e) {};
        $callback5 = function ($e) {};
        // @codingStandardsIgnoreEnd

        $this->events->attach('foo', $callback5, 1);
        $this->events->attach('foo', $callback1, 2);
        $this->events->attach('foo', $callback4, 3);
        $this->events->attach('foo', $callback3, 4);
        $this->events->attach('foo', $callback2, 5);

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertInstanceOf(Traversable::class, $listeners);
        $listeners = iterator_to_array($listeners);

        self::assertEquals([
            $callback5,
            $callback1,
            $callback4,
            $callback3,
            $callback2,
        ], $listeners);
    }

    public function testGetListenersForEventReturnsIteratorOfListenersInAttachmentOrderWhenSamePriority()
    {
        // @codingStandardsIgnoreStart
        $callback1 = function ($e) {};
        $callback2 = function ($e) {};
        $callback3 = function ($e) {};
        $callback4 = function ($e) {};
        $callback5 = function ($e) {};
        // @codingStandardsIgnoreEnd

        $this->events->attach('foo', $callback5);
        $this->events->attach('foo', $callback1);
        $this->events->attach('foo', $callback4);
        $this->events->attach('foo', $callback3);
        $this->events->attach('foo', $callback2);

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertInstanceOf(Traversable::class, $listeners);
        $listeners = iterator_to_array($listeners);

        self::assertEquals([
            $callback5,
            $callback1,
            $callback4,
            $callback3,
            $callback2,
        ], $listeners);
    }

    public function testGetListenersForEventCanReturnPriorityKeysWhenRequested()
    {
        // @codingStandardsIgnoreStart
        $callback1 = function ($e) {};
        $callback2 = function ($e) {};
        $callback3 = function ($e) {};
        $callback4 = function ($e) {};
        $callback5 = function ($e) {};
        // @codingStandardsIgnoreEnd

        $this->events->attach('foo', $callback5, 1);
        $this->events->attach('foo', $callback1, 2);
        $this->events->attach('foo', $callback4, 3);
        $this->events->attach('foo', $callback3, 4);
        $this->events->attach('foo', $callback2, 5);

        $listeners = $this->getListenersForEvent('foo', $this->events, true);
        self::assertInstanceOf(Traversable::class, $listeners);
        $listeners = iterator_to_array($listeners);

        self::assertEquals([
            1 => $callback5,
            2 => $callback1,
            3 => $callback4,
            4 => $callback3,
            5 => $callback2,
        ], $listeners);
    }

    public function testGetArrayOfListenersForEventReturnsArrayOfListenersInPriorityOrder()
    {
        // @codingStandardsIgnoreStart
        $callback1 = function ($e) {};
        $callback2 = function ($e) {};
        $callback3 = function ($e) {};
        $callback4 = function ($e) {};
        $callback5 = function ($e) {};
        // @codingStandardsIgnoreEnd

        $this->events->attach('foo', $callback5, 1);
        $this->events->attach('foo', $callback1, 1);
        $this->events->attach('foo', $callback4, 3);
        $this->events->attach('foo', $callback3, 2);
        $this->events->attach('foo', $callback2, 2);

        $listeners = $this->getArrayOfListenersForEvent('foo', $this->events);
        self::assertIsArray($listeners);

        self::assertEquals([
            $callback5,
            $callback1,
            $callback3,
            $callback2,
            $callback4,
        ], $listeners);
    }

    public function testAssertListenerAtPriorityPassesWhenListenerIsFound()
    {
        // @codingStandardsIgnoreStart
        $callback = function ($e) {};
        // @codingStandardsIgnoreEnd

        $this->events->attach('foo', $callback, 7);

        self::assertListenerAtPriority($callback, 7, 'foo', $this->events);
    }

    public function testAssertListenerAtPriorityFailsWhenListenerIsNotFound()
    {
        // @codingStandardsIgnoreStart
        $event = 'foo';
        $listener = function ($e) {};
        $priority = 7;
        $this->events->attach($event, $listener, $priority);

        $alternate = function ($e) {};

        $permutations = [
            'different-listener' => ['listener' => $alternate, 'priority' => $priority,     'event' => $event],
            'different-priority' => ['listener' => $listener,  'priority' => $priority + 1, 'event' => $event],
            'different-event'    => ['listener' => $listener,  'priority' => $priority,     'event' => $event . '-FOO'],
        ];
        // @codingStandardsIgnoreEnd

        foreach ($permutations as $case => $arguments) {
            try {
                self::assertListenerAtPriority(
                    $arguments['listener'],
                    $arguments['priority'],
                    $arguments['event'],
                    $this->events
                );
                $this->fail('assertListenerAtPriority assertion had a false positive for case ' . $case);
            } catch (ExpectationFailedException $e) {
                self::assertStringContainsString(sprintf(
                    'Listener not found for event "%s" and priority %d',
                    $arguments['event'],
                    $arguments['priority']
                ), $e->getMessage(), sprintf('Assertion failure message was unexpected: %s', $e->getMessage()));
            }
        }
    }
}
