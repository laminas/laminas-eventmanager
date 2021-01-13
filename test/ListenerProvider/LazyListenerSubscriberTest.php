<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\ListenerProvider\LazyListener;
use Laminas\EventManager\ListenerProvider\LazyListenerSubscriber;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerAttachmentInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class LazyListenerAggregateTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

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
            'array'      => [['listener']],
            'object'     => [(object) ['event' => 'event', 'listener' => 'listener', 'method' => 'method']],
        ];
    }

    /**
     * @dataProvider invalidListenerTypes
     * @param mixed $listener
     */
    public function testPassingInvalidListenerTypesAtInstantiationRaisesException($listener)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only accepts ' . LazyListener::class . ' instances');
        new LazyListenerSubscriber([$listener]);
    }

    public function testPassingLazyListenersMissingAnEventAtInstantiationRaisesException()
    {
        $listener = $this->prophesize(LazyListener::class);
        $listener->getEvent()->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('compose a non-empty string event');
        new LazyListenerSubscriber([$listener->reveal()]);
    }

    public function testAttachesLazyListenersToProviderUsingEventAndPriority()
    {
        $listener = $this->prophesize(LazyListener::class);
        $listener->getEvent()->willReturn('test');
        $listener->getPriority(1000)->willReturn(100);

        $subscriber = new LazyListenerSubscriber([$listener->reveal()]);

        $provider = $this->prophesize(PrioritizedListenerAttachmentInterface::class);
        $provider->attach('test', $listener->reveal(), 100)->shouldBeCalledTimes(1);

        $this->assertNull($subscriber->attach($provider->reveal(), 1000));

        return [
            'listener'   => $listener,
            'subscriber' => $subscriber,
            'provider'   => $provider,
        ];
    }

    /**
     * @depends testAttachesLazyListenersToProviderUsingEventAndPriority
     */
    public function testDetachesLazyListenersFromProviderUsingEvent(array $dependencies)
    {
        $listener   = $dependencies['listener'];
        $subscriber = $dependencies['subscriber'];
        $provider   = $dependencies['provider'];

        $provider->detach($listener->reveal(), 'test')->shouldBeCalledTimes(1);
        $this->assertNull($subscriber->detach($provider->reveal()));
    }
}
