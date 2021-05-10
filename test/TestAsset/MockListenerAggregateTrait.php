<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;

class MockListenerAggregateTrait implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach('foo.bar', [$this, 'doFoo']);
        $this->listeners[] = $events->attach('foo.baz', [$this, 'doFoo']);
    }

    public function getCallbacks()
    {
        return $this->listeners;
    }

    public function doFoo()
    {
    }
}
