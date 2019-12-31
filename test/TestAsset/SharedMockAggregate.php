<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\SharedListenerAggregateInterface;

/**
 * @group      Laminas_EventManager
 */
class SharedMockAggregate implements SharedListenerAggregateInterface
{

    protected $identity;

    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    protected $listeners = array();
    public $priority;

    public function attachShared(SharedEventManagerInterface $events, $priority = null)
    {
        $this->priority = $priority;

        $listeners = array();
        $listeners[] = $events->attach($this->identity, 'foo.bar', array( $this, 'fooBar' ));
        $listeners[] = $events->attach($this->identity, 'foo.baz', array( $this, 'fooBaz' ));

        $this->listeners[ \spl_object_hash($events) ] = $listeners;

        return __METHOD__;
    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->listeners[ \spl_object_hash($events) ] as $listener) {
            $events->detach($this->identity, $listener);
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
