<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\ListenerProvider\LazyListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class LazyListenerTest extends TestCase
{
    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function invalidListenerTypes()
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

    /**
     * @dataProvider invalidListenerTypes
     * @param mixed $listener
     */
    public function testConstructorRaisesExceptionForInvalidListenerType($listener)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a non-empty string $listener argument');
        new LazyListener($this->container->reveal(), $listener);
    }

    public function invalidMethodArguments(): array
    {
        return array_merge($this->invalidListenerTypes(), [
            'digit-first'     => ['0invalid'],
            'with-whitespace' => ['also invalid'],
            'with-dash'       => ['also-invalid'],
            'with-symbols'    => ['alsoInv@l!d'],
        ]);
    }

    /**
     * @dataProvider invalidMethodArguments
     * @param mixed $method
     */
    public function testConstructorRaisesExceptionForInvalidMethodArgument($method)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a valid string $method argument');
        new LazyListener($this->container->reveal(), 'valid-listener-name', $method);
    }

    public function testConstructorRaisesExceptionForInvalidEventArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a null or non-empty string $event argument');
        new LazyListener($this->container->reveal(), 'valid-listener-name', '__invoke', '');
    }

    public function testGetEventReturnsNullWhenNoEventProvidedToConstructor()
    {
        $listener = new LazyListener($this->container->reveal(), 'valid-listener-name');
        $this->assertNull($listener->getEvent());
    }

    public function testGetEventReturnsEventNameWhenEventProvidedToConstructor()
    {
        $listener = new LazyListener($this->container->reveal(), 'valid-listener-name', '__invoke', 'test');
        $this->assertEquals('test', $listener->getEvent());
    }

    public function testGetPriorityReturnsPriorityDefaultWhenNoPriorityProvidedToConstructor()
    {
        $listener = new LazyListener($this->container->reveal(), 'valid-listener-name');
        $this->assertEquals(100, $listener->getPriority(100));
    }

    public function testGetPriorityReturnsIntegerPriorityValueWhenPriorityProvidedToConstructor()
    {
        $listener = new LazyListener($this->container->reveal(), 'valid-listener-name', '__invoke', 'test', 100);
        $this->assertEquals(100, $listener->getPriority());
    }

    public function testGetPriorityReturnsIntegerPriorityValueWhenPriorityProvidedToConstructorAndToMethod()
    {
        $listener = new LazyListener($this->container->reveal(), 'valid-listener-name', '__invoke', 'test', 100);
        $this->assertEquals(100, $listener->getPriority(1000));
    }

    public function methodsToInvoke(): array
    {
        return [
            '__invoke' => ['__invoke', '__invoke'],
            'run'      => ['run', 'run'],
            'onEvent'  => ['onEvent', 'onEvent'],
        ];
    }

    /**
     * @dataProvider methodsToInvoke
     */
    public function testInvocationInvokesMethodDefinedInListener(string $method, string $expected)
    {
        $listener = new class {
            public function __invoke(object $e): void
            {
                $e->value = __FUNCTION__;
            }

            public function run(object $e): void
            {
                $e->value = __FUNCTION__;
            }

            public function onEvent(object $e): void
            {
                $e->value = __FUNCTION__;
            }
        };

        $this->container
            ->get('listener')
            ->willReturn($listener)
            ->shouldBeCalledTimes(1);

        $event = (object) ['value' => null];

        $lazyListener = new LazyListener($this->container->reveal(), 'listener', $method);

        $lazyListener($event);

        $this->assertEquals($expected, $event->value);
    }
}
