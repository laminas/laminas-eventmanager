<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;

use function spl_object_hash;

/**
 * @group      Laminas_EventManager
 */
class MockAggregate implements ListenerAggregateInterface
{

    protected $listeners = [];
    public $priority;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->priority = $priority;

        $listeners = [];
        $listeners[] = $events->attach('foo.bar', [ $this, 'fooBar' ]);
        $listeners[] = $events->attach('foo.baz', [ $this, 'fooBaz' ]);

        $this->listeners[ spl_object_hash($events) ] = $listeners;

        return __METHOD__;
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners[ spl_object_hash($events) ] as $listener) {
            $events->detach($listener);
        }

        return __METHOD__;
    }

    public function fooBar()
    {
        return __METHOD__;
    }

    public function fooBaz()
    {
        return __METHOD__;
    }
}
