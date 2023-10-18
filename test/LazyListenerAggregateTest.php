<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyEventListener;
use Laminas\EventManager\LazyListenerAggregate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

use function array_shift;
use function in_array;

class LazyListenerAggregateTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public static function invalidListenerTypes(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'string'     => ['listener'],
            'object'     => [(object) ['event' => 'event', 'listener' => 'listener', 'method' => 'method']],
        ];
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public static function invalidListeners(): array
    {
        return [
            'missing-event'    => [
                [
                    'listener' => 'listener',
                    'method'   => 'method',
                ],
            ],
            'missing-listener' => [
                [
                    'event'  => 'event',
                    'method' => 'method',
                ],
            ],
            'missing-method'   => [
                [
                    'event'    => 'event',
                    'listener' => 'listener',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidListenerTypes
     * @param mixed $listener
     */
    public function testPassingInvalidListenerTypesAtInstantiationRaisesException($listener)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be LazyEventListener instances');
        new LazyListenerAggregate([$listener], $this->container);
    }

    /**
     * @dataProvider invalidListeners
     * @param mixed $listener
     */
    public function testPassingInvalidListenersAtInstantiationRaisesException($listener)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid');
        new LazyListenerAggregate([$listener], $this->container);
    }

    /**
     * @psalm-return array<array-key, callable|array{
     *     event: string,
     *     listener: string|object,
     *     method: string,
     *     priority: int
     * }> $listeners
     */
    public function testCanPassMixOfValidLazyEventListenerInstancesAndDefinitionsAtInstantiation(): array
    {
        $listeners = [
            [
                'event'    => 'event',
                'listener' => 'listener',
                'method'   => 'method',
                'priority' => 5,
            ],
            new LazyEventListener([
                'event'    => 'event2',
                'listener' => 'listener2',
                'method'   => 'method2',
            ], $this->container),
        ];

        $aggregate = new LazyListenerAggregate($listeners, $this->container);

        $r    = new ReflectionProperty($aggregate, 'lazyListeners');
        $test = $r->getValue($aggregate);

        self::assertInstanceOf(LazyEventListener::class, $test[0]);
        self::assertEquals('event', $test[0]->getEvent());
        self::assertSame(
            $listeners[1],
            $test[1],
            'LazyEventListener instance changed during instantiation'
        );
        return $listeners;
    }

    /**
     * @depends testCanPassMixOfValidLazyEventListenerInstancesAndDefinitionsAtInstantiation
     * @psalm-param array<array-key, callable|array{
     *     event: string,
     *     listener: string|object,
     *     method: string,
     *     priority: int
     * }> $listeners
     */
    public function testAttachAttachesLazyListenersViaClosures(array $listeners)
    {
        $aggregate = new LazyListenerAggregate($listeners, $this->container);
        $events    = $this->createMock(EventManagerInterface::class);
        $events->expects(self::exactly(2))
            ->method('attach')
            ->with(
                self::callback(static function (string $event): bool {
                    self::assertTrue(in_array($event, ['event', 'event2']));
                    return true;
                }),
                self::callback(static function ($value): bool {
                    self::assertIsCallable($value);
                    return true;
                }),
                self::callback(static function (int $priority): bool {
                    self::assertTrue($priority === 5 || $priority === 7);

                    return true;
                }),
            );

        $aggregate->attach($events, 7);
    }

    public function testListenersArePulledFromContainerAndInvokedWhenTriggered(): void
    {
        $listener = $this->createMock(TestAsset\BuilderInterface::class);
        $listener->expects(self::once())
            ->method('build')
            ->with(self::isInstanceOf(EventInterface::class));

        $event = $this->createMock(EventInterface::class);

        $this->container->expects(self::once())
            ->method('get')
            ->with('listener')
            ->willReturn($listener);

        $events = $this->createMock(EventManagerInterface::class);
        $events->expects(self::once())
            ->method('attach')
            ->with('event', self::isType('callable'), 1)
            ->willReturnArgument(1);

        $listeners = [
            [
                'event'    => 'event',
                'listener' => 'listener',
                'method'   => 'build',
            ],
        ];

        $aggregate = new LazyListenerAggregate($listeners, $this->container);
        $aggregate->attach($events);

        $r         = new ReflectionProperty($aggregate, 'listeners');
        $listeners = $r->getValue($aggregate);

        self::assertIsArray($listeners);
        self::assertCount(1, $listeners);
        $listener = array_shift($listeners);
        self::assertInstanceOf(LazyEventListener::class, $listener);
        $listener($event);
    }
}
