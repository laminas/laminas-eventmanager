<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\Exception;
use Laminas\EventManager\SharedEventManager;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function sprintf;
use function var_export;

class SharedEventManagerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->callback = function ($e) {
        };
        $this->manager = new SharedEventManager();
    }

    public function getListeners(SharedEventManager $manager, array $identifiers, $event, $priority = 1)
    {
        $priority = (int) $priority;
        $listeners = $manager->getListeners($identifiers, $event);
        if (! isset($listeners[$priority])) {
            return [];
        }
        return $listeners[$priority];
    }

    public function invalidIdentifiers()
    {
        return [
            'null'                   => [null],
            'true'                   => [true],
            'false'                  => [false],
            'zero'                   => [0],
            'int'                    => [1],
            'zero-float'             => [0.0],
            'float'                  => [1.1],
            'empty-string'           => [''],
            'array'                  => [['test', 'foo']],
            'non-traversable-object' => [(object) ['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider invalidIdentifiers
     */
    public function testAttachRaisesExceptionForInvalidIdentifer($identifier)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('identifier');
        $this->manager->attach($identifier, 'foo', $this->callback);
    }

    public function invalidEventNames()
    {
        return [
            'null'                   => [null],
            'true'                   => [true],
            'false'                  => [false],
            'zero'                   => [0],
            'int'                    => [1],
            'zero-float'             => [0.0],
            'float'                  => [1.1],
            'empty-string'           => [''],
            'array'                  => [['foo', 'bar']],
            'non-traversable-object' => [(object) ['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider invalidEventNames
     */
    public function testAttachRaisesExceptionForInvalidEvent($event)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('event');
        $this->manager->attach('foo', $event, $this->callback);
    }

    public function testCanAttachToSharedManager()
    {
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);

        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertSame([$this->callback], $listeners);
    }

    public function detachIdentifierAndEvent()
    {
        return [
            'null-identifier-and-null-event' => [null, null],
            'same-identifier-and-null-event' => ['IDENTIFIER', null],
            'null-identifier-and-same-event' => [null, 'EVENT'],
            'same-identifier-and-same-event' => ['IDENTIFIER', 'EVENT'],
        ];
    }

    /**
     * @dataProvider detachIdentifierAndEvent
     */
    public function testCanDetachFromSharedManagerUsingIdentifierAndEvent($identifier, $event)
    {
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->detach($this->callback, $identifier, $event);
        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertSame([], $listeners);
    }

    public function testDetachDoesNothingIfIdentifierNotInManager()
    {
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->detach($this->callback, 'DIFFERENT-IDENTIFIER');

        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertSame([$this->callback], $listeners);
    }

    public function testDetachDoesNothingIfIdentifierDoesNotContainEvent()
    {
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->detach($this->callback, 'IDENTIFIER', 'DIFFERENT-EVENT');
        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertSame([$this->callback], $listeners);
    }

    public function testWhenEventIsProvidedAndNoListenersFoundForIdentiferGetListenersWillReturnEmptyList()
    {
        $test = $this->manager->getListeners([ 'IDENTIFIER' ], 'EVENT');
        self::assertIsArray($test);
        self::assertCount(0, $test);
    }

    public function testWhenEventIsProvidedGetListenersReturnsAllListenersIncludingWildcardListeners()
    {
        $callback1 = clone $this->callback;
        $callback2 = clone $this->callback;
        $callback3 = clone $this->callback;
        $callback4 = clone $this->callback;

        $this->manager->attach('IDENTIFIER', 'EVENT', $callback1);
        $this->manager->attach('IDENTIFIER', '*', $callback2);
        $this->manager->attach('*', 'EVENT', $callback3);
        $this->manager->attach('IDENTIFIER', 'EVENT', $callback4);

        $test = $this->getListeners($this->manager, [ 'IDENTIFIER' ], 'EVENT');
        self::assertEquals([
            $callback1,
            $callback4,
            $callback2,
            $callback3,
        ], $test);
    }

    public function testClearListenersWhenNoEventIsProvidedRemovesAllListenersForTheIdentifier()
    {
        $wildcardIdentifier = clone $this->callback;
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->attach('IDENTIFIER', '*', $this->callback);
        $this->manager->attach('*', 'EVENT', $wildcardIdentifier);
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);

        $this->manager->clearListeners('IDENTIFIER');

        $listeners = $this->getListeners($this->manager, [ 'IDENTIFIER' ], 'EVENT');
        self::assertSame(
            [$wildcardIdentifier],
            $listeners,
            sprintf(
                'Listener list should contain only wildcard identifier listener; received: %s',
                var_export($listeners, 1)
            )
        );
    }

    public function testClearListenersRemovesAllExplicitListenersForGivenIdentifierAndEvent()
    {
        $alternate = clone $this->callback;
        $wildcard  = clone $this->callback;
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->attach('IDENTIFIER', 'ALTERNATE', $alternate);
        $this->manager->attach('*', 'EVENT', $wildcard);
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);

        $this->manager->clearListeners('IDENTIFIER', 'EVENT');

        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertIsArray($listeners, 'Unexpected return value from getListeners() for event EVENT');
        self::assertCount(1, $listeners);
        $listener = array_shift($listeners);
        self::assertSame($wildcard, $listener, sprintf(
            'Expected only wildcard listener on event EVENT after clearListener operation; received: %s',
            var_export($listener, 1)
        ));

        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'ALTERNATE');
        self::assertIsArray(
            $listeners,
            'Unexpected return value from getListeners() for event ALTERNATE'
        );
        self::assertCount(1, $listeners);
        $listener = array_shift($listeners);
        self::assertSame($alternate, $listener, 'Unexpected listener list for event ALTERNATE');
    }

    public function testClearListenersDoesNotRemoveWildcardListenersWhenEventIsProvided()
    {
        $wildcardEventListener = clone $this->callback;
        $wildcardIdentifierListener = clone $this->callback;
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->manager->attach('IDENTIFIER', '*', $wildcardEventListener);
        $this->manager->attach('*', 'EVENT', $wildcardIdentifierListener);
        $this->manager->attach('IDENTIFIER', 'EVENT', $this->callback);

        $this->manager->clearListeners('IDENTIFIER', 'EVENT');

        $listeners = $this->getListeners($this->manager, ['IDENTIFIER'], 'EVENT');
        self::assertContains(
            $wildcardEventListener,
            $listeners,
            'Event listener list after clear operation does not include wildcard event listener'
        );
        self::assertContains(
            $wildcardIdentifierListener,
            $listeners,
            'Event listener list after clear operation does not include wildcard identifier listener'
        );
        self::assertNotContains(
            $this->callback,
            $listeners,
            'Event listener list after clear operation includes explicitly attached listener and should not'
        );
    }

    public function testClearListenersDoesNothingIfNoEventsRegisteredForIdentifier()
    {
        $callback = clone $this->callback;
        $this->manager->attach('IDENTIFIER', 'NOTEVENT', $this->callback);
        $this->manager->attach('*', 'EVENT', $this->callback);

        $this->manager->clearListeners('IDENTIFIER', 'EVENT');

        // getListeners() always pulls in wildcard listeners
        self::assertEquals([1 => [
            $this->callback,
        ]], $this->manager->getListeners([ 'IDENTIFIER' ], 'EVENT'));
    }

    public function invalidIdentifiersAndEvents()
    {
        $types = $this->invalidIdentifiers();
        unset($types['null']);
        return $types;
    }

    /**
     * @dataProvider invalidIdentifiersAndEvents
     */
    public function testDetachingWithInvalidIdentifierTypeRaisesException($identifier)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid identifier');
        $this->manager->detach($this->callback, $identifier, 'test');
    }

    /**
     * @dataProvider invalidIdentifiersAndEvents
     */
    public function testDetachingWithInvalidEventTypeRaisesException($eventName)
    {
        $this->manager->attach('IDENTIFIER', '*', $this->callback);
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event name');
        $this->manager->detach($this->callback, 'IDENTIFIER', $eventName);
    }

    public function invalidEventNamesForFetchingListeners()
    {
        $events = $this->invalidIdentifiers();
        $events['wildcard'] = ['*'];
        unset($events['non-traversable-object']);
        return $events;
    }

    /**
     * @dataProvider invalidEventNamesForFetchingListeners
     */
    public function testGetListenersRaisesExceptionForInvalidEventName($eventName)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty, non-wildcard');
        $this->manager->getListeners(['IDENTIFIER'], $eventName);
    }

    /**
     * @dataProvider invalidIdentifiers
     */
    public function testGetListenersRaisesExceptionForInvalidIdentifier($identifier)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');
        $this->manager->getListeners([$identifier], 'EVENT');
    }
}
