<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

use PHPUnit\Framework\TestCase;
use Laminas\EventManager\Exception;
use Laminas\EventManager\ListenerProvider\PrioritizedIdentifierListenerProvider;

class PrioritizedIdentifierListenerProviderTest extends TestCase
{
    public function setUp(): void
    {
        $this->callback = function (object $e): void {
        };
        $this->provider = new PrioritizedIdentifierListenerProvider();
    }

    /**
     * @param string[] $identifiers
     * @param string|object $event
     * @return callable[]
     */
    public function getListeners(
        PrioritizedIdentifierListenerProvider $provider,
        array $identifiers,
        $event,
        int $priority = 1
    ): iterable {
        $priority = (int) $priority;
        $listeners = $provider->getListenersForEventByPriority($event, $identifiers);
        if (! isset($listeners[$priority])) {
            return [];
        }
        return $listeners[$priority];
    }

    public function invalidIdentifiers(): array
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
     * @param mixed $identifier
     */
    public function testAttachRaisesExceptionForInvalidIdentifer($identifier)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('identifier');
        $this->provider->attach($identifier, 'foo', $this->callback);
    }

    public function invalidEventNames(): array
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
     * @param mixed $event
     */
    public function testAttachRaisesExceptionForInvalidEvent($event)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('event');
        $this->provider->attach('foo', $event, $this->callback);
    }

    public function testCanAttachListeners()
    {
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);

        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertSame([$this->callback], $listeners);
    }

    public function detachIdentifierAndEvent(): array
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
    public function testCanDetachListenersUsingIdentifierAndEvent(?string $identifier, ?string $event)
    {
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->detach($this->callback, $identifier, $event);
        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertSame([], $listeners);
    }

    public function testDetachDoesNothingIfIdentifierNotInProvider()
    {
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->detach($this->callback, 'DIFFERENT-IDENTIFIER');

        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertSame([$this->callback], $listeners);
    }

    public function testDetachDoesNothingIfIdentifierDoesNotContainEvent()
    {
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->detach($this->callback, 'IDENTIFIER', 'DIFFERENT-EVENT');
        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertSame([$this->callback], $listeners);
    }

    public function testProviderReturnsEmptyListWhenNoListenersAttachedForEventAndIdentifier()
    {
        $event = new class {
            public function getName(): string
            {
                return 'EVENT';
            }
        };
        $test = $this->provider->getListenersForEvent($event, ['IDENTIFIER']);
        $this->assertIsIterable($test);
        $this->assertCount(0, $test);
    }

    public function testProviderReturnsAllListenersIncludingWildcardListenersForEvent()
    {
        $callback1 = clone $this->callback;
        $callback2 = clone $this->callback;
        $callback3 = clone $this->callback;
        $callback4 = clone $this->callback;

        $this->provider->attach('IDENTIFIER', 'EVENT', $callback1);
        $this->provider->attach('IDENTIFIER', '*', $callback2);
        $this->provider->attach('*', 'EVENT', $callback3);
        $this->provider->attach('IDENTIFIER', 'EVENT', $callback4);

        $test = $this->getListeners($this->provider, [ 'IDENTIFIER' ], 'EVENT');
        $this->assertEquals([
            $callback1,
            $callback4,
            $callback2,
            $callback3,
        ], $test);
    }

    public function testClearListenersWhenNoEventIsProvidedRemovesAllListenersForTheIdentifier()
    {
        $wildcardIdentifier = clone $this->callback;
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->attach('IDENTIFIER', '*', $this->callback);
        $this->provider->attach('*', 'EVENT', $wildcardIdentifier);
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);

        $this->provider->clearListeners('IDENTIFIER');

        $listeners = $this->getListeners($this->provider, [ 'IDENTIFIER' ], 'EVENT');
        $this->assertSame(
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
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->attach('IDENTIFIER', 'ALTERNATE', $alternate);
        $this->provider->attach('*', 'EVENT', $wildcard);
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);

        $this->provider->clearListeners('IDENTIFIER', 'EVENT');

        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertIsArray($listeners, 'Unexpected return value from getListeners() for event EVENT');
        $this->assertCount(1, $listeners);
        $listener = array_shift($listeners);
        $this->assertSame($wildcard, $listener, sprintf(
            'Expected only wildcard listener on event EVENT after clearListener operation; received: %s',
            var_export($listener, 1)
        ));

        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'ALTERNATE');
        $this->assertIsArray(
            $listeners,
            'Unexpected return value from getListeners() for event ALTERNATE'
        );
        $this->assertCount(1, $listeners);
        $listener = array_shift($listeners);
        $this->assertSame($alternate, $listener, 'Unexpected listener list for event ALTERNATE');
    }

    public function testClearListenersDoesNotRemoveWildcardListenersWhenEventIsProvided()
    {
        $wildcardEventListener = clone $this->callback;
        $wildcardIdentifierListener = clone $this->callback;
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);
        $this->provider->attach('IDENTIFIER', '*', $wildcardEventListener);
        $this->provider->attach('*', 'EVENT', $wildcardIdentifierListener);
        $this->provider->attach('IDENTIFIER', 'EVENT', $this->callback);

        // REMOVE
        $this->provider->getListenersForEventByPriority('EVENT', ['IDENTIFIER']);

        $this->provider->clearListeners('IDENTIFIER', 'EVENT');

        $listeners = $this->getListeners($this->provider, ['IDENTIFIER'], 'EVENT');
        $this->assertContains(
            $wildcardEventListener,
            $listeners,
            'Event listener list after clear operation does not include wildcard event listener'
        );
        $this->assertContains(
            $wildcardIdentifierListener,
            $listeners,
            'Event listener list after clear operation does not include wildcard identifier listener'
        );
        $this->assertNotContains(
            $this->callback,
            $listeners,
            'Event listener list after clear operation includes explicitly attached listener and should not'
        );
    }

    public function testClearListenersDoesNothingIfNoEventsRegisteredForIdentifier()
    {
        $callback = clone $this->callback;
        $this->provider->attach('IDENTIFIER', 'NOTEVENT', $this->callback);
        $this->provider->attach('*', 'EVENT', $this->callback);

        $this->provider->clearListeners('IDENTIFIER', 'EVENT');

        // getListeners() always pulls in wildcard listeners
        $this->assertEquals([$this->callback], $this->getListeners($this->provider, [ 'IDENTIFIER' ], 'EVENT'));
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
        $this->provider->detach($this->callback, $identifier, 'test');
    }

    /**
     * @dataProvider invalidIdentifiersAndEvents
     */
    public function testDetachingWithInvalidEventTypeRaisesException($eventName)
    {
        $this->provider->attach('IDENTIFIER', '*', $this->callback);
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event name');
        $this->provider->detach($this->callback, 'IDENTIFIER', $eventName);
    }

    public function invalidEventNamesForFetchingListeners()
    {
        $types = $this->invalidEventNames();
        unset($types['non-traversable-object']);
        yield from $types;
    }

    /**
     * @dataProvider invalidEventNamesForFetchingListeners
     */
    public function testRetrievingListenersRaisesExceptionForInvalidEventName($eventName)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a non-empty');
        $this->provider->getListenersForEventByPriority($eventName, ['IDENTIFIER']);
    }

    /**
     * @dataProvider invalidIdentifiers
     */
    public function testRetrievingListenersRaisesExceptionForInvalidIdentifier($identifier)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be non-empty');
        $this->provider->getListenersForEventByPriority('EVENT', [$identifier]);
    }
}
