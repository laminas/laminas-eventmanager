<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;

/**
 * @category   Laminas
 * @package    Laminas_EventManager
 * @subpackage UnitTests
 * @group      Laminas_EventManager
 */
class MockAggregate implements ListenerAggregateInterface
{

    protected $listeners = array();
    public $priority;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->priority = $priority;

        $listeners = array();
        $listeners[] = $events->attach('foo.bar', array( $this, 'fooBar' ));
        $listeners[] = $events->attach('foo.baz', array( $this, 'fooBaz' ));

        $this->listeners[ \spl_object_hash($events) ] = $listeners;

        return __METHOD__;
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners[ \spl_object_hash($events) ] as $listener) {
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
