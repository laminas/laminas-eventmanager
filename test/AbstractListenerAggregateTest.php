<?php

namespace LaminasTest\EventManager;

class AbstractListenerAggregateTest extends ListenerAggregateTraitTest
{
    /** @var class-string */
    public $aggregateClass = TestAsset\MockAbstractListenerAggregate::class;
}
