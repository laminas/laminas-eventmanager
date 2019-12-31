<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\StaticEventManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group      Laminas_EventManager
 */
class StaticIntegrationTest extends TestCase
{
    public function setUp()
    {
        StaticEventManager::resetInstance();
    }

    public function testCanConnectStaticallyToClassWithEvents()
    {
        $counter = (object) array('count' => 0);
        $events  = StaticEventManager::getInstance();
        $events->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($counter) {
                $counter->count++;
            }
        );
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->setSharedManager($events);
        $class->foo();
        $this->assertEquals(1, $counter->count);
    }

    public function testLocalHandlersAreExecutedPriorToStaticHandlersWhenSetWithSamePriority()
    {
        $test = (object) array('results' => array());
        $events = StaticEventManager::getInstance();
        $events->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($test) {
                $test->results[] = 'static';
            }
        );
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->attach('foo', function ($e) use ($test) {
            $test->results[] = 'local';
        });
        $class->getEventManager()->setSharedManager($events);
        $class->foo();
        $this->assertEquals(array('local', 'static'), $test->results);
    }

    public function testLocalHandlersAreExecutedInPriorityOrderRegardlessOfStaticOrLocalRegistration()
    {
        $test = (object) array('results' => array());
        $events = StaticEventManager::getInstance();
        $events->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($test) {
                $test->results[] = 'static';
            },
            10000 // high priority
        );
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->attach('foo', function ($e) use ($test) {
            $test->results[] = 'local';
        }, 1); // low priority
        $class->getEventManager()->attach('foo', function ($e) use ($test) {
            $test->results[] = 'local2';
        }, 1000); // medium priority
        $class->getEventManager()->attach('foo', function ($e) use ($test) {
            $test->results[] = 'local3';
        }, 15000); // highest priority
        $class->getEventManager()->setSharedManager($events);
        $class->foo();
        $this->assertEquals(array('local3', 'static', 'local2', 'local'), $test->results);
    }

    public function testCallingUnsetSharedManagerDisablesStaticManager()
    {
        $counter = (object) array('count' => 0);
        StaticEventManager::getInstance()->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($counter) {
                $counter->count++;
            }
        );
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->unsetSharedManager();
        $class->foo();
        $this->assertEquals(0, $counter->count);
    }

    public function testCanPassAlternateStaticConnectionsHolder()
    {
        $counter = (object) array('count' => 0);
        StaticEventManager::getInstance()->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($counter) {
                $counter->count++;
            }
        );
        $mockStaticEvents = new TestAsset\StaticEventsMock();
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->setSharedManager($mockStaticEvents);
        $this->assertSame($mockStaticEvents, $class->getEventManager()->getSharedManager());
        $class->foo();
        $this->assertEquals(0, $counter->count);
    }

    public function testTriggerMergesPrioritiesOfStaticAndInstanceListeners()
    {
        $test = (object) array('results' => array());
        $events = StaticEventManager::getInstance();
        $events->attach(
            'LaminasTest\EventManager\TestAsset\ClassWithEvents',
            'foo',
            function ($e) use ($test) {
                $test->results[] = 'static';
            },
            100
        );
        $class = new TestAsset\ClassWithEvents();
        $class->getEventManager()->attach('foo', function ($e) use ($test) {
            $test->results[] = 'local';
        }, -100);
        $class->getEventManager()->setSharedManager($events);
        $class->foo();
        $this->assertEquals(array('static', 'local'), $test->results);
    }
}
