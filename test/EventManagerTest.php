<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\Exception;
use Laminas\EventManager\ListenerProvider;
use Laminas\EventManager\ResponseCollection;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionProperty;
use stdClass;

use function array_keys;
use function array_walk;
use function count;
use function get_class;
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
    use ProphecyTrait;

    protected function setUp() : void
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->events = new EventManager();
    }

    /**
     * Retrieve list of registered event names from a manager.
     *
     * @param EventManager $manager
     * @return string[]
     */
    public function getEventListFromManager(EventManager $manager)
    {
        $r = new ReflectionProperty($manager, 'events');
        $r->setAccessible(true);
        return array_keys($r->getValue($manager));
    }

    /**
     * Return listeners for a given event.
     *
     * @param string $event
     * @param EventManager $manager
     * @return array
     */
    public function getListenersForEvent($event, EventManager $manager)
    {
        $listeners = $manager->getListenersForEvent(new Event($event));
        return iterator_to_array($listeners, false);
    }

    public function testAttachShouldAddListenerToEvent()
    {
        $listener  = [$this, __METHOD__];
        $this->events->attach('test', $listener);
        $listeners = $this->getListenersForEvent('test', $this->events);
        self::assertCount(1, $listeners);
        self::assertContains($listener, $listeners);
        return [
            'event'    => 'test',
            'events'   => $this->events,
            'listener' => $listener,
        ];
    }

    public function eventArguments()
    {
        return [
            'single-named-event' => ['test'],
            'wildcard-event'     => ['*'],
        ];
    }

    /**
     * @dataProvider eventArguments
     */
    public function testAttachShouldAddReturnTheListener($event)
    {
        $listener  = [$this, __METHOD__];
        self::assertSame($listener, $this->events->attach($event, $listener));
    }

    public function testTriggerShouldTriggerAttachedListeners()
    {
        $this->events->attach('test', [$this, 'handleTestEvent']);
        $this->events->trigger('test', $this, ['message' => 'test message']);
        self::assertEquals('test message', $this->message);
    }

    public function testTriggerShouldReturnAllListenerReturnValues()
    {
        $this->events->attach('string.transform', function ($e) {
            $string = $e->getParam('string', '__NOT_FOUND__');
            return trim($string);
        });
        $this->events->attach('string.transform', function ($e) {
            $string = $e->getParam('string', '__NOT_FOUND__');
            return str_rot13($string);
        });
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertEquals(2, $responses->count());
        self::assertEquals('foo', $responses->first());
        self::assertEquals(str_rot13(' foo '), $responses->last());
    }

    public function testTriggerUntilShouldReturnAsSoonAsCallbackReturnsTrue()
    {
        $this->events->attach('foo.bar', function ($e) {
            $string = $e->getParam('string', '');
            $search = $e->getParam('search', '?');
            return strpos($string, $search);
        });
        $this->events->attach('foo.bar', function ($e) {
            $string = $e->getParam('string', '');
            $search = $e->getParam('search', '?');
            return strstr($string, $search);
        });
        $responses = $this->events->triggerUntil(
            [$this, 'evaluateStringCallback'],
            'foo.bar',
            $this,
            ['string' => 'foo', 'search' => 'f']
        );
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertSame(0, $responses->last());
    }

    public function testTriggerResponseCollectionContains()
    {
        $this->events->attach('string.transform', function ($e) {
            $string = $e->getParam('string', '');
            return trim($string);
        });
        $this->events->attach('string.transform', function ($e) {
            $string = $e->getParam('string', '');
            return str_rot13($string);
        });
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertTrue($responses->contains('foo'));
        self::assertTrue($responses->contains(str_rot13(' foo ')));
        self::assertFalse($responses->contains(' foo '));
    }

    public function handleTestEvent($e)
    {
        $message = $e->getParam('message', '__NOT_FOUND__');
        $this->message = $message;
    }

    public function evaluateStringCallback($value)
    {
        return (! $value);
    }

    public function testTriggerUntilShouldMarkResponseCollectionStoppedWhenConditionMet()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function () { return 'bogus'; }, 4);
        $this->events->attach('foo.bar', function () { return 'nada'; }, 3);
        $this->events->attach('foo.bar', function () { return 'found'; }, 2);
        $this->events->attach('foo.bar', function () { return 'zero'; }, 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(function ($result) {
            return ($result === 'found');
        }, 'foo.bar', $this);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        $result = $responses->last();
        self::assertEquals('found', $result);
        self::assertFalse($responses->contains('zero'));
    }

    public function testTriggerUntilShouldMarkResponseCollectionStoppedWhenConditionMetByLastListener()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function () { return 'bogus'; });
        $this->events->attach('foo.bar', function () { return 'nada'; });
        $this->events->attach('foo.bar', function () { return 'zero'; });
        $this->events->attach('foo.bar', function () { return 'found'; });
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(function ($result) {
            return ($result === 'found');
        }, 'foo.bar', $this);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        self::assertEquals('found', $responses->last());
    }

    public function testResponseCollectionIsNotStoppedWhenNoCallbackMatchedByTriggerUntil()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function () { return 'bogus'; }, 4);
        $this->events->attach('foo.bar', function () { return 'nada'; }, 3);
        $this->events->attach('foo.bar', function () { return 'found'; }, 2);
        $this->events->attach('foo.bar', function () { return 'zero'; }, 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->triggerUntil(function ($result) {
            return ($result === 'never found');
        }, 'foo.bar', $this);
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertFalse($responses->stopped());
        self::assertEquals('zero', $responses->last());
    }

    public function testCallingEventsStopPropagationMethodHaltsEventEmission()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function ($e) { return 'bogus'; }, 4);
        $this->events->attach('foo.bar', function ($e) { $e->stopPropagation(true); return 'nada'; }, 3);
        $this->events->attach('foo.bar', function ($e) { return 'found'; }, 2);
        $this->events->attach('foo.bar', function ($e) { return 'zero'; }, 1);
        // @codingStandardsIgnoreEnd

        $responses = $this->events->trigger('foo.bar');
        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertTrue($responses->stopped());
        self::assertEquals('nada', $responses->last());
        self::assertTrue($responses->contains('bogus'));
        self::assertFalse($responses->contains('found'));
        self::assertFalse($responses->contains('zero'));
    }

    public function testCanAlterParametersWithinAEvent()
    {
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function ($e) { $e->setParam('foo', 'bar'); });
        $this->events->attach('foo.bar', function ($e) { $e->setParam('bar', 'baz'); });
        // @codingStandardsIgnoreEnd
        $this->events->attach('foo.bar', function ($e) {
            $foo = $e->getParam('foo', '__NO_FOO__');
            $bar = $e->getParam('bar', '__NO_BAR__');
            return $foo . ":" . $bar;
        });

        $responses = $this->events->trigger('foo.bar');
        self::assertEquals('bar:baz', $responses->last());
    }

    public function testParametersArePassedToEventByReference()
    {
        $params = [ 'foo' => 'bar', 'bar' => 'baz'];
        $args   = $this->events->prepareArgs($params);

        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function ($e) { $e->setParam('foo', 'FOO'); });
        $this->events->attach('foo.bar', function ($e) { $e->setParam('bar', 'BAR'); });
        // @codingStandardsIgnoreEnd

        $responses = $this->events->trigger('foo.bar', $this, $args);
        self::assertEquals('FOO', $args['foo']);
        self::assertEquals('BAR', $args['bar']);
    }

    public function testCanPassObjectForEventParameters()
    {
        $params = (object) [ 'foo' => 'bar', 'bar' => 'baz'];
        // @codingStandardsIgnoreStart
        $this->events->attach('foo.bar', function ($e) { $e->setParam('foo', 'FOO'); });
        $this->events->attach('foo.bar', function ($e) { $e->setParam('bar', 'BAR'); });
        // @codingStandardsIgnoreEnd

        $responses = $this->events->trigger('foo.bar', $this, $params);
        self::assertEquals('FOO', $params->foo);
        self::assertEquals('BAR', $params->bar);
    }

    public function testCanPassEventObjectAsSoleArgumentToTriggerEvent()
    {
        $event = new Event();
        $event->setName(__FUNCTION__);
        $event->setTarget($this);
        $event->setParams(['foo' => 'bar']);
        $this->events->attach(__FUNCTION__, function ($e) {
            return $e;
        });
        $responses = $this->events->triggerEvent($event);
        self::assertSame($event, $responses->last());
    }

    public function testCanPassEventObjectAndCallbackToTriggerEventUntil()
    {
        $event = new Event();
        $event->setName(__FUNCTION__);
        $event->setTarget($this);
        $event->setParams(['foo' => 'bar']);
        $this->events->attach(__FUNCTION__, function ($e) {
            return $e;
        });
        $responses = $this->events->triggerEventUntil(function ($r) {
            return ($r instanceof EventInterface);
        }, $event);
        self::assertTrue($responses->stopped());
        self::assertSame($event, $responses->last());
    }

    public function testIdentifiersAreNotInjectedWhenNoSharedManagerProvided()
    {
        $events = new EventManager(null, [__CLASS__, get_class($this)]);
        $identifiers = $events->getIdentifiers();
        self::assertIsArray($identifiers);
        self::assertEmpty($identifiers);
    }

    public function testDuplicateIdentifiersAreNotRegistered()
    {
        $sharedEvents = $this->prophesize(SharedEventManagerInterface::class)->reveal();
        $events = new EventManager($sharedEvents, [__CLASS__, get_class($this)]);
        $identifiers = $events->getIdentifiers();
        self::assertSame(count($identifiers), 1);
        self::assertSame($identifiers[0], __CLASS__);
        $events->addIdentifiers([__CLASS__]);
        self::assertSame(count($identifiers), 1);
        self::assertSame($identifiers[0], __CLASS__);
    }

    public function testIdentifierGetterSetters()
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

    public function testListenersAttachedWithWildcardAreTriggeredForAllEvents()
    {
        $test         = new stdClass;
        $test->events = [];
        $callback     = function ($e) use ($test) {
            $test->events[] = $e->getName();
        };

        $this->events->attach('*', $callback);

        foreach (['foo', 'bar', 'baz'] as $event) {
            $this->events->trigger($event);
            self::assertContains($event, $test->events);
        }
    }

    public function testTriggerSetsStopPropagationFlagToFalse()
    {
        $marker = (object) ['propagationIsStopped' => true];
        $this->events->attach('foo', function ($e) use ($marker) {
            $marker->propagationIsStopped = $e->propagationIsStopped();
        });

        $event = new Event();
        $event->setName('foo');
        $event->stopPropagation(true);
        $this->events->triggerEvent($event);

        self::assertFalse($marker->propagationIsStopped);
        self::assertFalse($event->propagationIsStopped());
    }

    public function testTriggerEventUntilSetsStopPropagationFlagToFalse()
    {
        $marker = (object) ['propagationIsStopped' => true];
        $this->events->attach('foo', function ($e) use ($marker) {
            $marker->propagationIsStopped = $e->propagationIsStopped();
        });

        $criteria = function ($r) {
            return false;
        };
        $event = new Event();
        $event->setName('foo');
        $event->stopPropagation(true);
        $this->events->triggerEventUntil($criteria, $event);

        self::assertFalse($marker->propagationIsStopped);
        self::assertFalse($event->propagationIsStopped());
    }

    public function testCreatesAnEventPrototypeAtInstantiation()
    {
        self::assertAttributeInstanceOf(EventInterface::class, 'eventPrototype', $this->events);
    }

    public function testSetEventPrototype()
    {
        $event = $this->prophesize(EventInterface::class)->reveal();
        $this->events->setEventPrototype($event);

        self::assertAttributeSame($event, 'eventPrototype', $this->events);
    }

    public function testSharedManagerClearListenersReturnsFalse()
    {
        $shared = new SharedEventManager();
        self::assertFalse($shared->clearListeners('foo'));
    }

    public function testResponseCollectionLastReturnsNull()
    {
        $responses = $this->events->trigger('string.transform', $this, ['string' => ' foo ']);
        self::assertNull($responses->last());
    }

    public function testCanAddWildcardListenersAfterFirstTrigger()
    {
        $this->events->attach('foo', function ($e) {
            self::assertEquals('foo', $e->getName());
        });
        $this->events->trigger('foo');

        $triggered = false;
        $this->events->attach('*', function ($e) use (&$triggered) {
            self::assertEquals('foo', $e->getName());
            $triggered = true;
        });
        $this->events->trigger('foo');
        self::assertTrue($triggered, 'Wildcard listener was not triggered');
    }

    public function testCanInjectSharedManagerDuringConstruction()
    {
        $shared = $this->prophesize(SharedEventManagerInterface::class)->reveal();
        $events = new EventManager($shared);

        $r = new ReflectionProperty($events, 'provider');
        $r->setAccessible(true);
        $provider = $r->getValue($events);

        self::assertInstanceOf(ListenerProvider\PrioritizedAggregateListenerProvider::class, $provider);

        $r = new ReflectionProperty($provider, 'default');
        $r->setAccessible(true);
        $decorator = $r->getValue($provider);

        self::assertInstanceOf(SharedEventManager\SharedEventManagerDecorator::class, $decorator);

        $r = new ReflectionProperty($decorator, 'proxy');
        $r->setAccessible(true);
        $test = $r->getValue($decorator);

        self::assertSame($shared, $test);
    }

    public function invalidEventsForAttach()
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

    /**
     * @dataProvider invalidEventsForAttach
     */
    public function testAttachRaisesExceptionForInvalidEventType($event)
    {
        $callback = function () {
        };
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');
        $this->events->attach($event, $callback);
    }

    public function testCanClearAllListenersForAnEvent()
    {
        $events   = ['foo', 'bar', 'baz'];
        $listener = function ($e) {
        };
        foreach ($events as $event) {
            $this->events->attach($event, $listener);
        }

        $this->events->clearListeners('foo');

        self::assertCount(
            0,
            $this->getListenersForEvent('foo', $this->events),
            'Event foo listeners were not cleared'
        );

        foreach (['bar', 'baz'] as $event) {
            self::assertCount(
                1,
                $this->getListenersForEvent($event, $this->events),
                sprintf(
                    'Event %s listeners were cleared and should not have been',
                    $event
                )
            );
        }
    }

    public function testWillTriggerSharedListeners()
    {
        $name      = __FUNCTION__;
        $triggered = false;

        $shared = new SharedEventManager();
        $shared->attach(__CLASS__, $name, function ($event) use ($name, &$triggered) {
            self::assertEquals($name, $event->getName());
            $triggered = true;
        });

        $events = new EventManager($shared, [__CLASS__]);

        $events->trigger(__FUNCTION__);
        self::assertTrue($triggered, 'Shared listener was not triggered');
    }

    public function testWillTriggerSharedWildcardListeners()
    {
        $name      = __FUNCTION__;
        $triggered = false;

        $shared = new SharedEventManager();
        $shared->attach('*', $name, function ($event) use ($name, &$triggered) {
            self::assertEquals($name, $event->getName());
            $triggered = true;
        });

        $events = new EventManager($shared, [__CLASS__]);

        $events->trigger(__FUNCTION__);
        self::assertTrue($triggered, 'Shared listener was not triggered');
    }

    /**
     * @depends testAttachShouldAddListenerToEvent
     */
    public function testCanDetachListenerFromNamedEvent($dependencies)
    {
        $event    = $dependencies['event'];
        $events   = $dependencies['events'];
        $listener = $dependencies['listener'];

        $events->detach($listener, $event);

        $listeners = $this->getListenersForEvent($event, $events);
        self::assertCount(0, $listeners);
        self::assertNotContains($listener, $listeners);
    }

    public function testDetachDoesNothingIfEventIsNotPresentInManager()
    {
        $callback = function ($e) {
        };
        $this->events->attach('foo', $callback);
        $this->events->detach($callback, 'bar');
        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertContains($callback, $listeners);
    }

    /**
     * @group fail
     */
    public function testCanDetachWildcardListeners()
    {
        $events   = ['foo', 'bar'];
        $listener = function ($e) {
            return 'non-wildcard';
        };
        $wildcardListener = function ($e) {
            return 'wildcard';
        };

        array_walk($events, function ($event) use ($listener) {
            $this->events->attach($event, $listener);
        });
        $this->events->attach('*', $wildcardListener);

        $this->events->detach($wildcardListener, '*'); // Semantically the same as null

        // First, check the wildcard event queue
        $listeners = $this->getListenersForEvent('*', $this->events);
        self::assertEmpty($listeners);

        // Next, verify it's not in any of the specific event queues
        foreach ($events as $event) {
            $listeners = $this->getListenersForEvent($event, $this->events);
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
     * @depends testCanDetachWildcardListeners
     */
    public function testDetachedWildcardListenerWillNotBeTriggered($dependencies)
    {
        $eventNames  = $dependencies['event_names'];
        $events      = $dependencies['events'];
        $notContains = $dependencies['not_contains'];

        foreach ($eventNames as $event) {
            $results = $events->trigger($event);
            self::assertFalse($results->contains($notContains), 'Discovered unexpected wildcard value in results');
        }
    }

<<<<<<< HEAD
    public function testNotPassingEventNameToDetachDetachesListenerFromAllEvents()
    {
        $eventNames = ['foo', 'bar'];
        $events     = $this->events;
        $listener   = function ($e) {
            return 'listener';
        };

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

=======
>>>>>>> 22d9703... qa: ensure tests pass, and BC breaks identified and removed
    public function testCanDetachASingleListenerFromAnEventWithMultipleListeners()
    {
        $listener = function ($e) {
        };
        $alternateListener = clone $listener;

        $this->events->attach('foo', $listener);
        $this->events->attach('foo', $alternateListener);

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(
            2,
            $listeners,
            sprintf(
                'Listener count after attaching alternate listener for event %s was unexpected: %s',
                'foo',
                var_export($listeners, 1)
            )
        );
        self::assertContains($listener, $listeners);
        self::assertContains($alternateListener, $listeners);

        $this->events->detach($listener, 'foo');

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(
            1,
            $listeners,
            sprintf(
                "Listener count after detaching listener for event %s was unexpected;\nListeners: %s",
                'foo',
                var_export($listeners, 1)
            )
        );
        self::assertNotContains($listener, $listeners);
        self::assertContains($alternateListener, $listeners);
    }

    public function invalidEventsForDetach()
    {
        $events = $this->invalidEventsForAttach();
        unset($events['null']);
        return $events;
    }

    /**
     * @dataProvider invalidEventsForDetach
     */
    public function testPassingInvalidEventTypeToDetachRaisesException($event)
    {
        $listener = function ($e) {
        };

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');
        $this->events->detach($listener, $event);
    }

    public function testDetachRemovesAllOccurrencesOfListenerForEvent()
    {
        $listener = function ($e) {
        };

        for ($i = 0; $i < 5; $i += 1) {
            $this->events->attach('foo', $listener, $i);
        }

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(5, $listeners, var_export($listeners, true));

        $this->events->detach($listener, 'foo');

        $listeners = $this->getListenersForEvent('foo', $this->events);
        self::assertCount(0, $listeners);
        self::assertNotContains($listener, $listeners);
    }

    public function testTriggerEventAcceptsEventInstanceAndTriggersListeners()
    {
        $event = $this->prophesize(EventInterface::class);
        $event->getName()->willReturn('test');
        $event->stopPropagation(false)->shouldBeCalled();
        $event->propagationIsStopped()->willReturn(false);

        $triggered = false;
        $this->events->attach('test', function ($e) use ($event, &$triggered) {
            self::assertSame($event->reveal(), $e);
            $triggered = true;
        });

        $this->events->triggerEvent($event->reveal());
        self::assertTrue($triggered, 'Listener for event was not triggered');
    }

    public function testTriggerEventUntilAcceptsEventInstanceAndTriggersListenersUntilCallbackEvaluatesTrue()
    {
        $event = $this->prophesize(EventInterface::class);
        $event->getName()->willReturn('test');
        $event->stopPropagation(false)->shouldBeCalled();
        $event->propagationIsStopped()->willReturn(false);

        $callback = function ($result) {
            return ($result === true);
        };

        $triggeredOne = false;
        $this->events->attach('test', function ($e) use ($event, &$triggeredOne) {
            self::assertSame($event->reveal(), $e);
            $triggeredOne = true;
        });

        $triggeredTwo = false;
        $this->events->attach('test', function ($e) use ($event, &$triggeredTwo) {
            self::assertSame($event->reveal(), $e);
            $triggeredTwo = true;
            return true;
        });

        $this->events->attach('test', function ($e) {
            $this->fail('Third listener was triggered and should not have been');
        });

        $this->events->triggerEventUntil($callback, $event->reveal());
        self::assertTrue($triggeredOne, 'First Listener for event was not triggered');
        self::assertTrue($triggeredTwo, 'First Listener for event was not triggered');
    }
}
