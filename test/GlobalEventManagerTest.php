<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\GlobalEventManager;

/**
 * @group      Laminas_EventManager
 */
class GlobalEventManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        GlobalEventManager::setEventCollection(null);
    }

    public function testStoresAnEventManagerInstanceByDefault()
    {
        $events = GlobalEventManager::getEventCollection();
        $this->assertInstanceOf('Laminas\EventManager\EventManager', $events);
    }

    public function testPassingNullValueForEventCollectionResetsInstance()
    {
        $events = GlobalEventManager::getEventCollection();
        $this->assertInstanceOf('Laminas\EventManager\EventManager', $events);
        GlobalEventManager::setEventCollection(null);
        $events2 = GlobalEventManager::getEventCollection();
        $this->assertInstanceOf('Laminas\EventManager\EventManager', $events2);
        $this->assertNotSame($events, $events2);
    }

    public function testProxiesAllStaticOperationsToEventCollectionInstance()
    {
        $test     = new \stdClass();
        $listener = GlobalEventManager::attach('foo.bar', function ($e) use ($test) {
            $test->event  = $e->getName();
            $test->target = $e->getTarget();
            $test->params = $e->getParams();
            return $test->params;
        });
        $this->assertInstanceOf('Laminas\Stdlib\CallbackHandler', $listener);

        GlobalEventManager::trigger('foo.bar', $this, array('foo' => 'bar'));
        $this->assertSame($this, $test->target);
        $this->assertEquals('foo.bar', $test->event);
        $this->assertEquals(array('foo' => 'bar'), $test->params);

        $results = GlobalEventManager::trigger('foo.bar', $this, array('baz' => 'bat'), function ($r) {
            return is_array($r);
        });

        $this->assertTrue($results->stopped());
        $this->assertEquals(array('baz' => 'bat'), $test->params);
        $this->assertEquals(array('baz' => 'bat'), $results->last());

        $events = GlobalEventManager::getEvents();
        $this->assertEquals(array('foo.bar'), $events);

        $listeners = GlobalEventManager::getListeners('foo.bar');
        $this->assertEquals(1, count($listeners));
        $this->assertTrue($listeners->contains($listener));

        GlobalEventManager::detach($listener);
        $events = GlobalEventManager::getEvents();
        $this->assertEquals(array(), $events);

        $listener = GlobalEventManager::attach('foo.bar', function ($e) use ($test) {
            $test->event  = $e->getEvent();
            $test->target = $e->getTarget();
            $test->params = $e->getParams();
        });
        $events = GlobalEventManager::getEvents();
        $this->assertEquals(array('foo.bar'), $events);
        GlobalEventManager::clearListeners('foo.bar');
        $events = GlobalEventManager::getEvents();
        $this->assertEquals(array(), $events);
    }

    public function testTriggerUntilDeprecated()
    {
        $deprecated = null;
        set_error_handler(function () use (&$deprecated) {
            $deprecated = true;
        }, E_USER_DEPRECATED);

        GlobalEventManager::triggerUntil('foo.bar', $this, array('foo' => 'bar'), function () {});
        restore_error_handler();

        $this->assertTrue($deprecated, 'GlobalEventManager::triggerUntil not marked as E_USER_DEPRECATED');
    }
}
