<?php

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use LaminasTest\EventManager\TestAsset\MockEventManagerAwareTrait;
use PHPUnit\Framework\TestCase;

class EventManagerAwareTraitTest extends TestCase
{
    use DeprecatedAssertions;

    public function testSetEventManager(): void
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        self::assertAttributeEquals(null, 'events', $object);

        $eventManager = new EventManager();

        $object->setEventManager($eventManager);

        self::assertAttributeEquals($eventManager, 'events', $object);
    }

    public function testGetEventManager(): void
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        self::assertInstanceOf(EventManagerInterface::class, $object->getEventManager());

        $eventManager = new EventManager();

        $object->setEventManager($eventManager);

        self::assertSame($eventManager, $object->getEventManager());
    }

    public function testSetEventManagerWithEventIdentifier(): void
    {
        $object       = new MockEventManagerAwareTrait();
        $eventManager = new EventManager();

        $eventIdentifier = 'foo';
        $object->setEventIdentifier($eventIdentifier);

        $object->setEventManager($eventManager);

        //check that the identifier has been added.
        self::assertContains($eventIdentifier, $eventManager->getIdentifiers());

        //check that the method attachDefaultListeners has been called
        self::assertTrue($object->defaultEventListenersCalled());
    }
}
