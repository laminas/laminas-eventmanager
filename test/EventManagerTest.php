<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Closure;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\Exception;
use Laminas\EventManager\ResponseCollection;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function array_keys;
use function array_shift;
use function array_values;
use function array_walk;
use function count;
use function sort;
use function sprintf;
use function str_rot13;
use function strpos;
use function strstr;
use function trim;
use function var_export;

class EventManagerTest extends TestCase
{
    use DeprecatedAssertions;

    private EventManager $events;

    private string|null $message = null;

    protected function setUp(): void
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->events = new EventManager();
    }

    /**
     * Retrieve list of registered event names from a manager.
     *
     * @return list<string>
     */
    private function getEventListFromManager(EventManager $manager): array
    {
        $r     = new ReflectionProperty($manager, 'events');
        $value = $r->getValue($manager);
        self::assertIsArray($value);
        $keys = array_keys($value);
        self::assertContainsOnly('string', $keys);
        /** @psalm-var list<string> */

        return $keys;
    }

    /**
     * Return listeners for a given event.
     *
     * @return array<int, list<Closure>>
     */
    private function getListenersForEvent(string $event, EventManager $manager): array
    {
        $r      = new ReflectionProperty($manager, 'events');
        $events = $r->getValue($manager);
        self::assertIsArray($events);

        $out                 = [];
        $listenersByPriority = $events[$event] ?? [];
        self::assertIsArray($listenersByPriority);
        foreach ($listenersByPriority as $priority => $listeners) {
            self::assertIsInt($priority);
            self::assertIsArray($listeners);
            self::assertArrayHasKey(0, $listeners);
            self::assertIsArray($listeners[0]);
            $list = array_values($listeners[0]);
            self::assertContainsOnlyInstancesOf(Closure::class, $list);
            /** @psalm-var list<Closure> $list */
            $out[$priority] = $list;
        }

        return $out;
    }

    /** @psalm-return array{event: "test", events: EventManager, listener: callable} */
    public function testAttachShouldAddListenerToEvent(): array
    {
        $listener  = static fn(): int => 0;
        $this->events->attach('test', $listener);
        $listeners = $this->getListenersForEvent('test', $this->events);
        // Get first (and only) priority queue of listeners for event
        $listeners = array_shift($listeners);
        self::assertCount(1, $listeners);
        self::assertContains($listener, $listeners);
        return [
            'event'    => 'test',
            'events'   => $this->events,
            'listener' => $listener,
        ];
    }

    /** @psalm-return array<string, array{0: string}> */
    public static function eventArguments(): array
    {
        return [
            'single-named-event' => ['test'],
            'wildcard-event'     => ['*'],
        ];
    }

    #[DataProvider('eventArguments')]
    public function testAttachShouldAddReturnTheListener(string $event): void
    {
        $listener = static fn(): int => 0;
        self::assertSame($listener, $this->events->attach($event, $listener));
    }

    public function testAttachShouldAddEventIfItDoesNotExist(): void
    {
        self::assertAttributeEmpty('events', $this->events);
        $this->events->attach('test', static fn () => null);
        $events = $this->getEventListFromManager($this->events);
        self::assertNotEmpty($events);
        self::assertContains('test', $events);
    }

    public function testTriggerShouldTriggerAttachedListeners(): void
    {
        $handler = function (EventInterface $e): void {
            $message = $e->getParam('message', '__NOT_FOUND__');
            self::assertIsString($message);
            $this->message = $message;
        };
        $this->events->attach('test', $handler);
        $this->events->trigger('test', $this, ['message' => 'test message']);
        self::assertEquals('test message', $this->message);
    }

    public function testTriggerShouldReturnAllListenerReturnValues(): void
    {
        $this->events->attach('string.transform', function (EventInterface $e): string {
            $string = $e->getParam('string', '__NOT_FOUND__');
            self::assertIsString($string);
            return trim($string);
        });
        $this->events->attach('string.transform', function (EventInterface $e): string {
            $string = $e->getParam('string', '__NOT_FOUND__');
            self::assertIsString($string);
            return str_rot13($string);
        });
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertEquals(2, $responses->count());
        self::assertEquals('foo', $responses->first());
        self::assertEquals(str_rot13(' foo '), $responses->last());
    }

    public function testTriggerUntilShouldReturnAsSoonAsCallbackReturnsTrue(): void
    {
        $this->events->attach('foo.bar', function (EventInterface $e) {
            $string = $e->getParam('string', '');
            self::assertIsString($string);
            $search = $e->getParam('search', '?');
            self::assertIsString($search);
            return strpos($string, $search);
        });
        $this->events->attach('foo.bar', function (EventInterface $e) {
            $string = $e->getParam('string', '');
            self::assertIsString($string);
            $search = $e->getParam('search', '?');
            self::assertIsString($search);
            return strstr($string, $search);
        });
        $responses = $this->events->triggerUntil(
            $this->evaluateStringCallback(...),
            'foo.bar',
            $this,
            ['string' => 'foo', 'search' => 'f'],
        );
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertSame(0, $responses->last());
    }

    public function testTriggerResponseCollectionContains(): void
    {
        $this->events->attach('string.transform', function (EventInterface $e): string {
            $string = $e->getParam('string', '');
            self::assertIsString($string);
            return trim($string);
        });
        $this->events->attach('string.transform', function (EventInterface $e): string {
            $string = $e->getParam('string', '');
            self::assertIsString($string);
            return str_rot13($string);
        });
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertTrue($responses->contains('foo'));
        self::assertTrue($responses->contains(str_rot13(' foo ')));
        self::assertFalse($responses->contains(' foo '));
    }

    public function evaluateStringCallback(mixed $value): bool
    {
        return ! $value;
    }

    public function testTriggerUntilShouldMarkResponseCollectionStoppedWhenConditionMet(): void
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', fn() => 'bogus', 4);
        $this->events->attach('foo.bar', fn() => 'nada', 3);
        $this->events->attach('foo.bar', fn() => 'found', 2);
        $this->events->attach('foo.bar', fn() => 'zero', 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(fn($result) => $result === 'found', 'foo.bar', $this);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        $result = $responses->last();
        self::assertIsString($result);
        self::assertEquals('found', $result);
        self::assertFalse($responses->contains('zero'));
    }

    public function testTriggerUntilShouldMarkResponseCollectionStoppedWhenConditionMetByLastListener(): void
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', fn() => 'bogus');
        $this->events->attach('foo.bar', fn() => 'nada');
        $this->events->attach('foo.bar', fn() => 'zero');
        $this->events->attach('foo.bar', fn() => 'found');
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(fn($result): bool => $result === 'found', 'foo.bar', $this);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        self::assertEquals('found', $responses->last());
    }

    public function testResponseCollectionIsNotStoppedWhenNoCallbackMatchedByTriggerUntil(): void
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', static fn() => 'bogus', 4);
        $this->events->attach('foo.bar', static fn() => 'nada', 3);
        $this->events->attach('foo.bar', static fn() => 'found', 2);
        $this->events->attach('foo.bar', static fn() => 'zero', 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(
            static fn(mixed $result): bool =>
            $result === 'never found',
            'foo.bar',
            $this
        );
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertFalse($responses->stopped());
        self::assertEquals('zero', $responses->last());
    }

    public function testCallingEventsStopPropagationMethodHaltsEventEmission(): void
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', static fn(): string => 'bogus', 4);
        $this->events->attach('foo.bar', static function (EventInterface $e): string { $e->stopPropagation(true); return 'nada'; }, 3);
        $this->events->attach('foo.bar', static fn(): string => 'found', 2);
        $this->events->attach('foo.bar', static fn(): string => 'zero', 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->trigger('foo.bar');
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        self::assertEquals('nada', $responses->last());
        self::assertTrue($responses->contains('bogus'));
        self::assertFalse($responses->contains('found'));
        self::assertFalse($responses->contains('zero'));
    }

    public function testCanAlterParametersWithinAEvent(): void
    {
        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('foo', 'bar');
        });
        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('bar', 'baz');
        });
        $this->events->attach('foo.bar', static function (EventInterface $e): string {
            $foo = $e->getParam('foo', '__NO_FOO__');
            self::assertIsString($foo);
            $bar = $e->getParam('bar', '__NO_BAR__');
            self::assertIsString($bar);
            return $foo . ":" . $bar;
        });

        $responses = $this->events->trigger('foo.bar');
        self::assertEquals('bar:baz', $responses->last());
    }

    public function testParametersArePassedToEventByReference(): void
    {
        $params = ['foo' => 'bar', 'bar' => 'baz'];
        $args   = $this->events->prepareArgs($params);

        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('foo', 'FOO');
        });
        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('bar', 'BAR');
        });

        $this->events->trigger('foo.bar', $this, $args);
        self::assertEquals('FOO', $args['foo']);
        self::assertEquals('BAR', $args['bar']);
    }

    public function testCanPassObjectForEventParameters(): void
    {
        $params = (object) ['foo' => 'bar', 'bar' => 'baz'];
        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('foo', 'FOO');
        });
        $this->events->attach('foo.bar', static function (EventInterface $e): void {
            $e->setParam('bar', 'BAR');
        });

        $this->events->trigger('foo.bar', $this, $params);
        self::assertEquals('FOO', $params->foo);
        self::assertEquals('BAR', $params->bar);
    }

    public function testCanPassEventObjectAsSoleArgumentToTriggerEvent(): void
    {
        $event = new Event();
        $event->setName(__FUNCTION__);
        $event->setTarget($this);
        $event->setParams(['foo' => 'bar']);
        $this->events->attach(__FUNCTION__, static fn(EventInterface $e): EventInterface => $e);
        $responses = $this->events->triggerEvent($event);
        self::assertSame($event, $responses->last());
    }

    public function testCanPassEventObjectAndCallbackToTriggerEventUntil(): void
    {
        $event = new Event();
        $event->setName(__FUNCTION__);
        $event->setTarget($this);
        $event->setParams(['foo' => 'bar']);
        $this->events->attach(
            __FUNCTION__,
            static fn(EventInterface $e): EventInterface => $e
        );
        $responses = $this->events->triggerEventUntil(
            static fn(mixed $r): bool =>
            $r instanceof EventInterface,
            $event
        );
        self::assertTrue($responses->stopped());
        self::assertSame($event, $responses->last());
    }

    public function testIdentifiersAreNotInjectedWhenNoSharedManagerProvided(): void
    {
        $events      = new EventManager(null, [self::class, static::class]);
        $identifiers = $events->getIdentifiers();
        self::assertEmpty($identifiers);
    }

    public function testDuplicateIdentifiersAreNotRegistered(): void
    {
        $sharedEvents = $this->createMock(SharedEventManagerInterface::class);
        $events       = new EventManager($sharedEvents, [self::class, static::class]);
        $identifiers  = $events->getIdentifiers();
        self::assertSame(count($identifiers), 1);
        self::assertSame($identifiers[0], self::class);
        $events->addIdentifiers([self::class]);
        self::assertSame(count($identifiers), 1);
        self::assertSame($identifiers[0], self::class);
    }

    public function testIdentifierGetterSetters(): void
    {
        $identifiers = ['foo', 'bar'];
        $this->events->setIdentifiers($identifiers);
        self::assertSame($this->events->getIdentifiers(), $identifiers);
        $identifiers[] = 'baz';
        $this->events->addIdentifiers($identifiers);

        // This is done because the keys do not matter, just the values
        $expectedIdentifiers = $this->events->getIdentifiers();
        sort($expectedIdentifiers);
        sort($identifiers);
        self::assertSame($expectedIdentifiers, $identifiers);
    }

    public function testListenersAttachedWithWildcardAreTriggeredForAllEvents(): void
    {
        $test         = new stdClass();
        $test->events = [];
        $callback     = static function (EventInterface $e) use ($test): void {
            $test->events[] = $e->getName();
        };

        $this->events->attach('*', $callback);

        foreach (['foo', 'bar', 'baz'] as $event) {
            $this->events->trigger($event);
            self::assertContains($event, $test->events);
        }
    }

    public function testTriggerSetsStopPropagationFlagToFalse(): void
    {
        $marker = (object) ['propagationIsStopped' => true];
        $this->events->attach('foo', static function (EventInterface $e) use ($marker): void {
            $marker->propagationIsStopped = $e->propagationIsStopped();
        });

        $event = new Event();
        $event->setName('foo');
        $event->stopPropagation(true);
        $this->events->triggerEvent($event);

        self::assertFalse($marker->propagationIsStopped);
        self::assertFalse($event->propagationIsStopped());
    }

    public function testTriggerEventUntilSetsStopPropagationFlagToFalse(): void
    {
        $marker = (object) ['propagationIsStopped' => true];
        $this->events->attach('foo', static function (EventInterface $e) use ($marker) {
            $marker->propagationIsStopped = $e->propagationIsStopped();
        });

        $criteria = static fn(): bool => false;
        $event    = new Event();
        $event->setName('foo');
        $event->stopPropagation(true);
        $this->events->triggerEventUntil($criteria, $event);

        self::assertFalse($marker->propagationIsStopped);
        self::assertFalse($event->propagationIsStopped());
    }

    public function testCreatesAnEventPrototypeAtInstantiation(): void
    {
        self::assertAttributeInstanceOf(EventInterface::class, 'eventPrototype', $this->events);
    }

    public function testSetEventPrototype(): void
    {
        $event = $this->createMock(EventInterface::class);
        $this->events->setEventPrototype($event);

        self::assertAttributeSame($event, 'eventPrototype', $this->events);
    }

    public function testSharedManagerClearListenersReturnsFalse(): void
    {
        $shared = new SharedEventManager();
        self::assertFalse($shared->clearListeners('foo'));
    }

    public function testResponseCollectionLastReturnsNull(): void
    {
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertNull($responses->last());
    }

    public function testCanAddWildcardListenersAfterFirstTrigger(): void
    {
        $this->events->attach('foo', static function (EventInterface $e): void {
            self::assertEquals('foo', $e->getName());
        });
        $this->events->trigger('foo');

        $triggered = false;
        $this->events->attach('*', static function (EventInterface $e) use (&$triggered): void {
            self::assertEquals('foo', $e->getName());
            $triggered = true;
        });
        $this->events->trigger('foo');
        self::assertTrue($triggered, 'Wildcard listener was not triggered');
    }

    public function testCanInjectSharedManagerDuringConstruction(): void
    {
        $shared = $this->createMock(SharedEventManagerInterface::class);
        $events = new EventManager($shared);
        self::assertSame($shared, $events->getSharedManager());
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public static function invalidEventsForAttach(): array
    {
        return [
            'null'                   => [null],
            'true'                   => [true],
            'false'                  => [false],
            'zero'                   => [0],
            'int'                    => [1],
            'zero-float'             => [0.0],
            'float'                  => [1.1],
            'array'                  => [['test1', 'test2']],
            'non-traversable-object' => [(object) ['foo' => 'bar']],
        ];
    }

    #[DataProvider('invalidEventsForAttach')]
    public function testAttachRaisesExceptionForInvalidEventType(mixed $event): void
    {
        $callback = static function (): void {
        };
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');
        /** @psalm-suppress MixedArgument */
        $this->events->attach($event, $callback);
    }

    public function testCanClearAllListenersForAnEvent(): void
    {
        $events   = ['foo', 'bar', 'baz'];
        $listener = static function (): void {
        };
        foreach ($events as $event) {
            $this->events->attach($event, $listener);
        }

        self::assertEquals($events, $this->getEventListFromManager($this->events));
        $this->events->clearListeners('foo');
        self::assertCount(
            0,
            $this->getListenersForEvent('foo', $this->events),
            'Event foo listeners were not cleared',
        );

        foreach (['bar', 'baz'] as $event) {
            self::assertCount(
                1,
                $this->getListenersForEvent($event, $this->events),
                sprintf(
                    'Event %s listeners were cleared and should not have been',
                    $event,
                ),
            );
        }
    }

    public function testWillTriggerSharedListeners(): void
    {
        $name      = __FUNCTION__;
        $triggered = false;

        $shared = new SharedEventManager();
        $shared->attach(self::class, $name, static function (EventInterface $event) use ($name, &$triggered): void {
            self::assertEquals($name, $event->getName());
            $triggered = true;
        });

        $events = new EventManager($shared, [self::class]);

        $events->trigger(__FUNCTION__);
        self::assertTrue($triggered, 'Shared listener was not triggered');
    }

    public function testWillTriggerSharedWildcardListeners(): void
    {
        $name      = __FUNCTION__;
        $triggered = false;

        $shared = new SharedEventManager();
        $shared->attach('*', $name, static function (EventInterface $event) use ($name, &$triggered): void {
            self::assertEquals($name, $event->getName());
            $triggered = true;
        });

        $events = new EventManager($shared, [self::class]);

        $events->trigger(__FUNCTION__);
        self::assertTrue($triggered, 'Shared listener was not triggered');
    }

    /**
     * @psalm-param array{event: 'test', events: EventManager, listener: callable} $dependencies
     */
    #[Depends('testAttachShouldAddListenerToEvent')]
    public function testCanDetachListenerFromNamedEvent(array $dependencies): void
    {
        $event    = $dependencies['event'];
        $events   = $dependencies['events'];
        $listener = $dependencies['listener'];

        $events->detach($listener, $event);

        $listeners = $this->getListenersForEvent($event, $events);
        self::assertCount(0, $listeners);
        self::assertNotContains($listener, $listeners);
    }

    public function testDetachDoesNothingIfEventIsNotPresentInManager(): void
    {
        $callback = static function (): void {
        };
        $this->events->attach('foo', $callback);
        $this->events->detach($callback, 'bar');
        $listeners = $this->getListenersForEvent('foo', $this->events);
        // get first (and only) priority queue from listeners
        $listeners = array_shift($listeners);
        self::assertContains($callback, $listeners);
    }

    /** @return array{event_names: list<string>, events: EventManager, not_contains: string} */
    public function testCanDetachWildcardListeners(): array
    {
        $events           = ['foo', 'bar'];
        $listener         = static fn(): string => 'non-wildcard';
        $wildcardListener = static fn(): string => 'wildcard';

        array_walk($events, function (string $event) use ($listener): void {
            $this->events->attach($event, $listener);
        });
        $this->events->attach('*', $wildcardListener);

        $this->events->detach($wildcardListener, '*'); // Semantically the same as null

        // First, check the wildcard event queue
        $listeners = $this->getListenersForEvent('*', $this->events);
        self::assertEmpty($listeners);

        // Next, verify it's not in any of the specific event queues
        /** @psalm-var list<string> $events */
        foreach ($events as $event) {
            $listeners = $this->getListenersForEvent($event, $this->events);
            // Get listeners for first and only priority queue
            $listeners = array_shift($listeners);
            self::assertCount(1, $listeners);
            self::assertNotContains($wildcardListener, $listeners);
        }

        return [
            'event_names'  => $events,
            'events'       => $this->events,
            'not_contains' => 'wildcard',
        ];
    }

    /**
     * @psalm-param array{event_names: list<string>, events: EventManager, not_contains: string} $dependencies
     */
    #[Depends('testCanDetachWildcardListeners')]
    public function testDetachedWildcardListenerWillNotBeTriggered(array $dependencies): void
    {
        $eventNames  = $dependencies['event_names'];
        $events      = $dependencies['events'];
        $notContains = $dependencies['not_contains'];

        foreach ($eventNames as $event) {
            $results = $events->trigger($event);
            self::assertFalse($results->contains($notContains), 'Discovered unexpected wildcard value in results');
        }
    }

    public function testNotPassingEventNameToDetachDetachesListenerFromAllEvents(): void
    {
        $eventNames = ['foo', 'bar'];
        $events     = $this->events;
        $listener   = static fn(): string => 'listener';

        foreach ($eventNames as $event) {
            $events->attach($event, $listener);
        }

        $events->detach($listener);

        foreach ($eventNames as $event) {
            $listeners = $this->getListenersForEvent($event, $events);
            self::assertCount(0, $listeners);
            self::assertNotContains($listener, $listeners);
        }
    }

    public function testCanDetachASingleListenerFromAnEventWithMultipleListeners(): void
    {
        $listener          = static function (): void {
        };
        $alternateListener = clone $listener;

        $this->events->attach('foo', $listener);
        $this->events->attach('foo', $alternateListener);

        $listeners = $this->getListenersForEvent('foo', $this->events);
        // Get the listeners for the first priority queue
        $listeners = array_shift($listeners);
        self::assertCount(
            2,
            $listeners,
            sprintf(
                'Listener count after attaching alternate listener for event %s was unexpected: %s',
                'foo',
                var_export($listeners, true),
            ),
        );
        self::assertContains($listener, $listeners);
        self::assertContains($alternateListener, $listeners);

        $this->events->detach($listener, 'foo');

        $listeners = $this->getListenersForEvent('foo', $this->events);
        // Get the listeners for the first priority queue
        $listeners = array_shift($listeners);
        self::assertCount(
            1,
            $listeners,
            sprintf(
                "Listener count after detaching listener for event %s was unexpected;\nListeners: %s",
                'foo',
                var_export($listeners, true),
            ),
        );
        self::assertNotContains($listener, $listeners);
        self::assertContains($alternateListener, $listeners);
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public static function invalidEventsForDetach(): array
    {
        $events = self::invalidEventsForAttach();
        unset($events['null']);
        return $events;
    }

    #[DataProvider('invalidEventsForDetach')]
    public function testPassingInvalidEventTypeToDetachRaisesException(mixed $event): void
    {
        $listener = static function (): void {
        };

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');
        /** @psalm-suppress MixedArgument */
        $this->events->detach($listener, $event);
    }

    public function testDetachRemovesAllOccurrencesOfListenerForEvent(): void
    {
        $listener = static function (): void {
        };

        for ($i = 0; $i < 5; $i += 1) {
            $this->events->attach('foo', $listener, $i);
        }

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(5, $listeners);

        $this->events->detach($listener, 'foo');

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(0, $listeners);
        self::assertNotContains($listener, $listeners);
    }

    /** @psalm-return array<string, array{0: string|null, 1: string, 2: null|callable}> */
    public static function eventsMissingNames(): array
    {
        $callback = static function (): void {
        };

        // @codingStandardsIgnoreStart
        //                                      [ event,             method to trigger, callback ]
        return [
            'trigger-empty-string'           => ['',     'trigger',           null],
            'trigger-until-empty-string'     => ['',     'triggerUntil',      $callback],
            'trigger-event-empty-name'       => [null,   'triggerEvent',      null],
            'trigger-event-until-empty-name' => [null,   'triggerEventUntil', $callback],
        ];
        // @codingStandardsIgnoreEnd
    }

    #[DataProvider('eventsMissingNames')]
    public function testTriggeringAnEventWithAnEmptyNameRaisesAnException(
        string|null $event,
        string $method,
        ?callable $callback
    ): void {
        if ($event === null) {
            $event = $this->createMock(EventInterface::class);
            $event->expects(self::atLeast(1))
                ->method('getName')
                ->willReturn('');
        }

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('missing a name');
        if ($callback !== null) {
            $this->events->$method($callback, $event);
        } else {
            $this->events->$method($event);
        }
    }

    public function testTriggerEventAcceptsEventInstanceAndTriggersListeners(): void
    {
        $event = $this->createMock(EventInterface::class);
        $event->expects(self::atLeast(1))
            ->method('getName')
            ->willReturn('test');

        $event->expects(self::once())
            ->method('stopPropagation')
            ->with(false);

        $event->expects(self::atLeast(1))
            ->method('propagationIsStopped')
            ->willReturn(false);

        $triggered = false;
        $this->events->attach('test', static function (EventInterface $e) use ($event, &$triggered): void {
            self::assertSame($event, $e);
            $triggered = true;
        });

        $this->events->triggerEvent($event);
        self::assertTrue($triggered, 'Listener for event was not triggered');
    }

    public function testTriggerEventUntilAcceptsEventInstanceAndTriggersListenersUntilCallbackEvaluatesTrue(): void
    {
        $event = $this->createMock(EventInterface::class);
        $event->expects(self::atLeast(1))
            ->method('getName')
            ->willReturn('test');

        $event->expects(self::once())
            ->method('stopPropagation')
            ->with(false);

        $event->expects(self::atLeast(1))
            ->method('propagationIsStopped')
            ->willReturn(false);

        $callback = static fn(mixed $result): bool => $result === true;

        $triggeredOne = false;
        $this->events->attach('test', static function (EventInterface $e) use ($event, &$triggeredOne): void {
            self::assertSame($event, $e);
            $triggeredOne = true;
        });

        $triggeredTwo = false;
        $this->events->attach('test', static function (EventInterface $e) use ($event, &$triggeredTwo): bool {
            self::assertSame($event, $e);
            $triggeredTwo = true;
            return true;
        });

        $this->events->attach('test', function () {
            self::fail('Third listener was triggered and should not have been');
        });

        $this->events->triggerEventUntil($callback, $event);
        self::assertTrue($triggeredOne, 'First Listener for event was not triggered');
        self::assertTrue($triggeredTwo, 'First Listener for event was not triggered');
    }
}
