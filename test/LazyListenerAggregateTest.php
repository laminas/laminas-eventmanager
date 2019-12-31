<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\LazyEventListener;
use Laminas\EventManager\LazyListenerAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use ReflectionProperty;

class LazyListenerAggregateTest extends TestCase
{
    public function setUp()
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
            'string'     => ['listener'],
            'object'     => [(object) ['event' => 'event', 'listener' => 'listener', 'method' => 'method']],
        ];
    }

    public function invalidListeners()
    {
        return [
            'missing-event' => [
                [
                    'listener' => 'listener',
                    'method'   => 'method',
                ]
            ],
            'missing-listener' => [
                [
                    'event'  => 'event',
                    'method' => 'method',
                ]
            ],
            'missing-method' => [
                [
                    'event'    => 'event',
                    'listener' => 'listener',
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidListenerTypes
     */
    public function testPassingInvalidListenerTypesAtInstantiationRaisesException($listener)
    {
        $this->setExpectedException(InvalidArgumentException::class, 'must be LazyEventListener instances');
        new LazyListenerAggregate([$listener], $this->container->reveal());
    }

    /**
     * @dataProvider invalidListeners
     */
    public function testPassingInvalidListenersAtInstantiationRaisesException($listener)
    {
        $this->setExpectedException(InvalidArgumentException::class, 'missing a valid');
        new LazyListenerAggregate([$listener], $this->container->reveal());
    }

    public function testCanPassMixOfValidLazyEventListenerInstancesAndDefinitionsAtInstantiation()
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

        $this->assertInstanceOf(LazyEventListener::class, $test[0]);
        $this->assertEquals('event', $test[0]->getEvent());
        $this->assertSame($listeners[1], $test[1], 'LazyEventListener instance changed during instantiation');
        return $listeners;
    }

    /**
     * @depends testCanPassMixOfValidLazyEventListenerInstancesAndDefinitionsAtInstantiation
     */
    public function testAttachAttachesLazyListenersViaClosures($listeners)
    {
        $aggregate = new LazyListenerAggregate($listeners, $this->container->reveal());
        $events = $this->prophesize(EventManagerInterface::class);
        $events->attach('event', Argument::type('callable'), 5)->shouldBeCalled();
        $events->attach('event2', Argument::type('callable'), 7)->shouldBeCalled();

        $aggregate->attach($events->reveal(), 7);
    }

    public function testListenersArePulledFromContainerAndInvokedWhenTriggered()
    {
        $listener = $this->prophesize(TestAsset\BuilderInterface::class);
        $listener->build(Argument::type(EventInterface::class))->shouldBeCalled();

        $event = $this->prophesize(EventInterface::class);

        $this->container->get('listener')->will(function ($args) use ($listener) {
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

        $this->assertInternalType('array', $listeners);
        $this->assertCount(1, $listeners);
        $listener = array_shift($listeners);
        $this->assertInstanceOf(LazyEventListener::class, $listener);
        $listener($event->reveal());
    }
}
