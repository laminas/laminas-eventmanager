<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\ListenerAggregateInterface;

class AbstractListenerAggregateTest extends ListenerAggregateTraitTest
{
    /** @var class-string<ListenerAggregateInterface> */
    public $aggregateClass = TestAsset\MockAbstractListenerAggregate::class;
}
