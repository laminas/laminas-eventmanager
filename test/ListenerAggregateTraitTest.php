<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ListenerAggregateTraitTest extends TestCase
{
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
        $this->assertInternalType('array', $listeners);
        $this->assertCount(2, $listeners);

        foreach ($listeners as $listener) {
            $this->assertSame([$aggregate, 'doFoo'], $listener);
        }

        $aggregate->detach($events);

        $this->assertAttributeSame([], 'listeners', $aggregate);
    }
}
