<?php

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use stdClass;

class LazyListenerTest extends TestCase
{
    use DeprecatedAssertions;
    use ProphecyTrait;

    /** @var class-string */
    protected string $listenerClass;

    /** @var ObjectProphecy&ContainerInterface */
    protected $container;

    protected function setUp(): void
    {
        $this->listenerClass = LazyListener::class;
        $this->container     = $this->prophesize(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public function invalidTypes(): array
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

    public function testConstructorRaisesExceptionForMissingListener(): void
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
     * @param mixed $listener
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

    public function testConstructorRaisesExceptionForMissingMethod(): void
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
     * @param mixed $method
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

    public function testCanInstantiateLazyListenerWithValidDefinition(): LazyListener
    {
        $class  = $this->listenerClass;
        $struct = [
            'listener' => 'listener',
            'method'   => 'method',
        ];

        $listener = new $class($struct, $this->container->reveal());
        self::assertInstanceOf($class, $listener);
        return $listener;
    }

    /**
     * @depends testCanInstantiateLazyListenerWithValidDefinition
     */
    public function testInstatiationSetsListenerMethod(LazyListener $listener)
    {
        self::assertAttributeEquals('method', 'method', $listener);
    }

    public function testLazyListenerActsAsInvokableAroundListenerCreation(): void
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
        self::assertInstanceOf($class, $lazyListener);

        self::assertEquals('RECEIVED', $lazyListener($event->reveal()));
    }

    public function testInvocationWillDelegateToContainerBuildMethodWhenPresentAndEnvIsNonEmpty(): void
    {
        $class    = $this->listenerClass;
        $listener = $this->prophesize(TestAsset\BuilderInterface::class);
        $listener->build(Argument::type(EventInterface::class))->willReturn('RECEIVED');

        $event = $this->prophesize(EventInterface::class);

        $instance = new stdClass();
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
        self::assertInstanceOf($class, $lazyListener);

        self::assertEquals('RECEIVED', $lazyListener($event->reveal()));
    }
}
