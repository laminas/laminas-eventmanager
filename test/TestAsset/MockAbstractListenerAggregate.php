<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class MockAbstractListenerAggregate extends AbstractListenerAggregate
{
    /** @param int $priority */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach('foo.bar', [$this, 'doFoo']);
        $this->listeners[] = $events->attach('foo.baz', [$this, 'doFoo']);
    }

    /** @return callable[] */
    public function getCallbacks(): array
    {
        return $this->listeners;
    }

    public function doFoo()
    {
    }
}
