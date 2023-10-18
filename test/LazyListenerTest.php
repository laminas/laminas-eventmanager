<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class LazyListenerTest extends TestCase
{
    use DeprecatedAssertions;

    /** @var class-string */
    protected string $listenerClass;

    /** @var ContainerInterface&MockObject */
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->listenerClass = LazyListener::class;
        $this->container     = $this->createMock(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public static function invalidTypes(): array
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
        new $class($struct, $this->container);
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
        new $class($struct, $this->container);
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
        new $class($struct, $this->container);
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
        new $class($struct, $this->container);
    }

    public function testCanInstantiateLazyListenerWithValidDefinition(): LazyListener
    {
        $class  = $this->listenerClass;
        $struct = [
            'listener' => 'listener',
            'method'   => 'method',
        ];

        $listener = new $class($struct, $this->container);
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
        $listener = $this->createMock(TestAsset\BuilderInterface::class);
        $listener->expects(self::once())
            ->method('build')
            ->with(self::isInstanceOf(EventInterface::class))
            ->willReturn('RECEIVED');

        $event = $this->createMock(EventInterface::class);

        $this->container->expects(self::once())
            ->method('get')
            ->with('listener')
            ->willReturn($listener);

        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'build',
            'priority' => 5,
        ];

        $lazyListener = new $class($struct, $this->container);
        self::assertInstanceOf($class, $lazyListener);

        self::assertEquals('RECEIVED', $lazyListener($event));
    }

    public function testInvocationWillDelegateToContainerBuildMethodWhenPresentAndEnvIsNonEmpty(): void
    {
        $class    = $this->listenerClass;
        $listener = $this->createMock(TestAsset\BuilderInterface::class);
        $listener->expects(self::once())
            ->method('build')
            ->with(self::isInstanceOf(EventInterface::class))
            ->willReturn('RECEIVED');

        $event = $this->createMock(EventInterface::class);

        $env = [
            'foo' => 'bar',
        ];

        $container = $this->createMock(TestAsset\BuilderInterface::class);
        $container->expects(self::once())
            ->method('build')
            ->with('listener', $env)
            ->willReturn($listener);

        $struct = [
            'event'    => 'event',
            'listener' => 'listener',
            'method'   => 'build',
            'priority' => 5,
        ];

        $lazyListener = new $class($struct, $container, $env);
        self::assertInstanceOf($class, $lazyListener);

        self::assertEquals('RECEIVED', $lazyListener($event));
    }
}
