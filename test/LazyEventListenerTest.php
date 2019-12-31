<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyEventListener;

class LazyEventListenerTest extends LazyListenerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->listenerClass = LazyEventListener::class;
    }

    public function testConstructorRaisesExceptionForMissingEvent()
    {
        $class  = $this->listenerClass;
        $struct = [
            'listener' => 'listener',
            'method'   => 'method',
        ];
        $this->setExpectedException(InvalidArgumentException::class, 'missing a valid "event"');
        new $class($struct, $this->container->reveal());
    }

    /**
     * @dataProvider invalidTypes
     */
    public function testConstructorRaisesExceptionForInvalidEventType($event)
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => $event,
            'listener' => 'listener',
            'method'   => 'method',
        ];
        $this->setExpectedException(InvalidArgumentException::class, 'missing a valid "event"');
        new $class($struct, $this->container->reveal());
    }

    public function testCanInstantiateLazyListenerWithValidDefinition()
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'method',
            'priority' => 5,
        ];

        $listener = new $class($struct, $this->container->reveal());
        $this->assertInstanceOf($class, $listener);
        return $listener;
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testCanRetrieveEventFromListener($listener)
    {
        $this->assertEquals('event', $listener->getEvent());
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testCanRetrievePriorityFromListener($listener)
    {
        $this->assertEquals(5, $listener->getPriority());
    }

    public function testGetPriorityWillReturnProvidedPriorityIfNoneGivenAtInstantiation()
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'method',
        ];

        $listener = new $class($struct, $this->container->reveal());
        $this->assertInstanceOf($class, $listener);
        $this->assertEquals(5, $listener->getPriority(5));
    }
}
