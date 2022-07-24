<?php

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyEventListener;
use Laminas\EventManager\LazyListenerAggregate;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

use function array_shift;

class LazyListenerAggregateTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public function invalidListenerTypes(): array
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
    public function invalidListeners(): array
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
        new LazyListenerAggregate([$listener], $this->container->reveal());
    }

    /**
     * @dataProvider invalidListeners
     * @param mixed $listener
     */
    public function testPassingInvalidListenersAtInstantiationRaisesException($listener)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing a valid');
        new LazyListenerAggregate([$listener], $this->container->reveal());
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
            ], $this->container->reveal()),
        ];

        $aggregate = new LazyListenerAggregate($listeners, $this->container->reveal());

        $r = new ReflectionProperty($aggregate, 'lazyListeners');
        $r->setAccessible(true);
        $test = $r->getValue($aggregate);

        self::assertInstanceOf(LazyEventListener::class, $test[0]);
        self::assertEquals('event', $test[0]->getEvent());
        self::assertSame($listeners[1], $test[1], 'LazyEventListener instance changed during instantiation');
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
        $aggregate = new LazyListenerAggregate($listeners, $this->container->reveal());
        $events    = $this->prophesize(EventManagerInterface::class);
        $events->attach('event', Argument::type('callable'), 5)->shouldBeCalled();
        $events->attach('event2', Argument::type('callable'), 7)->shouldBeCalled();

        $aggregate->attach($events->reveal(), 7);
    }

    public function testListenersArePulledFromContainerAndInvokedWhenTriggered(): void
    {
        $listener = $this->prophesize(TestAsset\BuilderInterface::class);
        $listener->build(Argument::type(EventInterface::class))->shouldBeCalled();

        $event = $this->prophesize(EventInterface::class);

        $this->container->get('listener')->will(function () use ($listener) {
            return $listener->reveal();
        });

        $events = $this->prophesize(EventManagerInterface::class);
        $events->attach('event', Argument::type('callable'), 1)->will(function ($args) {
            return $args[1];
        });

        $listeners = [
            [
                'event'    => 'event',
                'listener' => 'listener',
                'method'   => 'build',
            ],
        ];

        $aggregate = new LazyListenerAggregate($listeners, $this->container->reveal());
        $aggregate->attach($events->reveal());

        $r = new ReflectionProperty($aggregate, 'listeners');
        $r->setAccessible(true);
        $listeners = $r->getValue($aggregate);

        self::assertIsArray($listeners);
        self::assertCount(1, $listeners);
        $listener = array_shift($listeners);
        self::assertInstanceOf(LazyEventListener::class, $listener);
        $listener($event->reveal());
    }
}
