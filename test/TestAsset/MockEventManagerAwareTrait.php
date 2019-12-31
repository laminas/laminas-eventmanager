<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerAwareTrait;

/**
 * @group      Laminas_EventManager
 */
class MockEventManagerAwareTrait
{
    use EventManagerAwareTrait;

    protected $eventIdentifier = 'foo.bar';
    protected $defaultEventListenersCalled = false;

    public function getEventIdentifier()
    {
        return $this->eventIdentifier;
    }

    public function setEventIdentifier($eventIdentifier)
    {
        $this->eventIdentifier = $eventIdentifier;
        return $this;
    }

    public function attachDefaultListeners()
    {
        $this->defaultEventListenersCalled = true;
    }

    public function defaultEventListenersCalled()
    {
        return $this->defaultEventListenersCalled;
    }
}
