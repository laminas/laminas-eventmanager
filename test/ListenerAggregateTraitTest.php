<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ListenerAggregateTraitTest extends TestCase
{
    use ProphecyTrait;

    public $aggregateClass = TestAsset\MockListenerAggregateTrait::class;

    public function testDetachRemovesAttachedListeners()
    {
        $class     = $this->aggregateClass;
        $aggregate = new $class();

        $prophecy = $this->prophesize(EventManagerInterface::class);
        $prophecy->attach('foo.bar', [$aggregate, 'doFoo'])->will(function ($args) {
            return $args[1];
        });
        $prophecy->attach('foo.baz', [$aggregate, 'doFoo'])->will(function ($args) {
            return $args[1];
        });
        $prophecy->detach([$aggregate, 'doFoo'])->shouldBeCalledTimes(2);
        $events = $prophecy->reveal();

        $aggregate->attach($events);

        $listeners = $aggregate->getCallbacks();
        self::assertIsArray($listeners);
        self::assertCount(2, $listeners);

        foreach ($listeners as $listener) {
            self::assertSame([$aggregate, 'doFoo'], $listener);
        }

        $aggregate->detach($events);

        self::assertSame([], $aggregate->getCallbacks());
    }
}
