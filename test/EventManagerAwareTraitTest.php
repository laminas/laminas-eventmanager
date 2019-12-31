<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use LaminasTest\EventManager\TestAsset\MockEventManagerAwareTrait;
use PHPUnit_Framework_TestCase as TestCase;

class EventManagerAwareTraitTest extends TestCase
{
    public function testSetEventManager()
    {
        $object = $this->getObjectForTrait('\Laminas\EventManager\EventManagerAwareTrait');

        $this->assertAttributeEquals(null, 'events', $object);

        $eventManager = new EventManager;

        $object->setEventManager($eventManager);

        $this->assertAttributeEquals($eventManager, 'events', $object);
    }

    public function testGetEventManager()
    {
        $object = $this->getObjectForTrait('\Laminas\EventManager\EventManagerAwareTrait');

        $this->assertInstanceOf('\Laminas\EventManager\EventManagerInterface', $object->getEventManager());

        $eventManager = new EventManager;

        $object->setEventManager($eventManager);

        $this->assertSame($eventManager, $object->getEventManager());
    }

    public function testSetEventManagerWithEventIdentifier()
    {
        $object = new MockEventManagerAwareTrait();
        $eventManager = new EventManager();

        $eventIdentifier = 'foo';
        $object->setEventIdentifier($eventIdentifier);

        $object->setEventManager($eventManager);

        //check that the identifer has been added.
        $this->assertContains($eventIdentifier, $eventManager->getIdentifiers());

        //check that the method attachDefaultListeners has been called
        $this->assertTrue($object->defaultEventListenersCalled());
    }
}
