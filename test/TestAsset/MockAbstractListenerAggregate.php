<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

/**
 * @group      Laminas_EventManager
 */
class MockAbstractListenerAggregate extends AbstractListenerAggregate
{
    public $priority;

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
