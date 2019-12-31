<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use \Laminas\EventManager\EventManager;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * @requires PHP 5.4
 */
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
}
