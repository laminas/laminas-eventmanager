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
use Laminas\EventManager\LazyListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

class LazyListenerTest extends TestCase
{
    public function setUp()
    {
        $this->listenerClass = LazyListener::class;
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function invalidTypes()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'empty'      => [''],
            'array'      => [['event']],
            'object'     => [(object) ['event' => 'event']],
        ];
    }

    public function testConstructorRaisesExceptionForMissingListener()
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'  => 'event',
            'method' => 'method',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "listener"');
        new $class($struct, $this->container->reveal());
    }

    /**
     * @dataProvider invalidTypes
     */
    public function testConstructorRaisesExceptionForInvalidListenerType($listener)
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => $listener,
            'method'   => 'method',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "listener"');
        new $class($struct, $this->container->reveal());
    }

    public function testConstructorRaisesExceptionForMissingMethod()
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "method"');
        new $class($struct, $this->container->reveal());
    }

    /**
     * @dataProvider invalidTypes
     */
    public function testConstructorRaisesExceptionForInvalidMethodType($method)
    {
        $class  = $this->listenerClass;
        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => $method,
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid "method"');
        new $class($struct, $this->container->reveal());
    }

    public function testCanInstantiateLazyListenerWithValidDefinition()
    {
        $class  = $this->listenerClass;
        $struct = [
            'listener' => 'listener',
            'method'   => 'method',
        ];

        $listener = new $class($struct, $this->container->reveal());
        $this->assertInstanceOf($class, $listener);
        return $listener;
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testInstatiationSetsListenerMethod($listener)
    {
        $this->assertAttributeEquals('method', 'method', $listener);
    }

    public function testLazyListenerActsAsInvokableAroundListenerCreation()
    {
        $class    = $this->listenerClass;
        $listener = $this->prophesize(TestAsset\BuilderInterface::class);
        $listener->build(Argument::type(EventInterface::class))->willReturn('RECEIVED');

        $event = $this->prophesize(EventInterface::class);

        $this->container->get('listener')->will(function ($args) use ($listener) {
            return $listener->reveal();
        });

        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'build',
            'priority' => 5,
        ];

        $lazyListener = new $class($struct, $this->container->reveal());
        $this->assertInstanceOf($class, $lazyListener);

        $this->assertEquals('RECEIVED', $lazyListener($event->reveal()));
    }

    public function testInvocationWillDelegateToContainerBuildMethodWhenPresentAndEnvIsNonEmpty()
    {
        $class    = $this->listenerClass;
        $listener = $this->prophesize(TestAsset\BuilderInterface::class);
        $listener->build(Argument::type(EventInterface::class))->willReturn('RECEIVED');

        $event = $this->prophesize(EventInterface::class);

        $instance = new stdClass;
        $env      = [
            'foo' => 'bar',
        ];

        $container = $this->prophesize(TestAsset\BuilderInterface::class);
        $container->build('listener', $env)->will(function ($args) use ($listener) {
            return $listener->reveal();
        });

        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'build',
            'priority' => 5,
        ];

        $lazyListener = new $class($struct, $container->reveal(), $env);
        $this->assertInstanceOf($class, $lazyListener);

        $this->assertEquals('RECEIVED', $lazyListener($event->reveal()));
    }
}
