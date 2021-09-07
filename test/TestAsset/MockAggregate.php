<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;

use function spl_object_hash;

/**
 * @group      Laminas_EventManager
 */
class MockAggregate implements ListenerAggregateInterface
{
    /** @var callable[] */
    protected $listeners = [];

    /** @var null|int */
    public $priority;

    /** @param int $priority */
    public function attach(EventManagerInterface $events, $priority = 1): string
    {
        $this->priority = $priority;

        $listeners   = [];
        $listeners[] = $events->attach('foo.bar', [$this, 'fooBar']);
        $listeners[] = $events->attach('foo.baz', [$this, 'fooBaz']);

        $this->listeners[ spl_object_hash($events) ] = $listeners;

        return __METHOD__;
    }

    public function detach(EventManagerInterface $events): string
    {
        foreach ($this->listeners[ spl_object_hash($events) ] as $listener) {
            $events->detach($listener);
        }

        return __METHOD__;
    }

    public function fooBar(): string
    {
        return __METHOD__;
    }

    public function fooBaz(): string
    {
        return __METHOD__;
    }
}
