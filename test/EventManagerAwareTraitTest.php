<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use LaminasTest\EventManager\TestAsset\MockEventManagerAwareTrait;
use PHPUnit\Framework\TestCase;

class EventManagerAwareTraitTest extends TestCase
{
    public function testSetEventManager()
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        $this->assertAttributeEquals(null, 'events', $object);

        $eventManager = new EventManager;

        $object->setEventManager($eventManager);

        $this->assertAttributeEquals($eventManager, 'events', $object);
    }

    public function testGetEventManager()
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        $this->assertInstanceOf(EventManagerInterface::class, $object->getEventManager());

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

        //check that the identifier has been added.
        $this->assertContains($eventIdentifier, $eventManager->getIdentifiers());

        //check that the method attachDefaultListeners has been called
        $this->assertTrue($object->defaultEventListenersCalled());
    }
}
