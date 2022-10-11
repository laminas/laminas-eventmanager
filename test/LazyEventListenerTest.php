<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyEventListener;
use Laminas\EventManager\LazyListener;

class LazyEventListenerTest extends LazyListenerTest
{
    use DeprecatedAssertions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listenerClass = LazyEventListener::class;
    }

    public function testConstructorRaisesExceptionForMissingEvent(): void
    {
        $class  = $this->listenerClass;
        $struct = [
            'listener' => 'listener',
            'method'   => 'method',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "event"');
        new $class($struct, $this->container);
    }

    /**
     * @dataProvider invalidTypes
     * @param mixed $event
     */
    public function testConstructorRaisesExceptionForInvalidEventType($event)
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => $event,
            'listener' => 'listener',
            'method'   => 'method',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "event"');
        new $class($struct, $this->container);
    }

    public function testCanInstantiateLazyListenerWithValidDefinition(): LazyListener
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'method',
            'priority' => 5,
        ];

        $listener = new $class($struct, $this->container);
        self::assertInstanceOf($class, $listener);
        return $listener;
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testCanRetrieveEventFromListener(LazyEventListener $listener)
    {
        self::assertEquals('event', $listener->getEvent());
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testCanRetrievePriorityFromListener(LazyEventListener $listener)
    {
        self::assertEquals(5, $listener->getPriority());
    }

    public function testGetPriorityWillReturnProvidedPriorityIfNoneGivenAtInstantiation(): void
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'method',
        ];

        $listener = new $class($struct, $this->container);
        self::assertInstanceOf($class, $listener);
        self::assertEquals(5, $listener->getPriority(5));
    }
}
