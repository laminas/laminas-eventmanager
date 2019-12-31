<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use LaminasTest\EventManager\TestAsset\MockAbstractListenerAggregate;

/**
 * @category   Laminas
 * @package    Laminas_EventManager
 * @subpackage UnitTests
 * @group      Laminas_EventManager
 */
class AbstractListenerAggregateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \LaminasTest\EventManager\TestAsset\MockAbstractListenerAggregate
     */
    protected $listener;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->listener = new MockAbstractListenerAggregate();
    }

    /**
     * @covers \Laminas\EventManager\AbstractListenerAggregate::detach
     */
    public function testDetach()
    {
        $eventManager          = $this->getMock('Laminas\\EventManager\\EventManagerInterface');
        $unrelatedEventManager = $this->getMock('Laminas\\EventManager\\EventManagerInterface');
        $callbackHandlers      = array();
        $test                  = $this;

        $eventManager
            ->expects($this->exactly(2))
            ->method('attach')
            ->will($this->returnCallback(function () use (&$callbackHandlers, $test) {
                return $callbackHandlers[] = $test->getMock('Laminas\\Stdlib\\CallbackHandler', array(), array(), '', false);
            }));

        $this->listener->attach($eventManager);
        $this->assertSame($callbackHandlers, $this->listener->getCallbacks());

        $this->listener->detach($unrelatedEventManager);

        $this->assertSame($callbackHandlers, $this->listener->getCallbacks());

        $eventManager
            ->expects($this->exactly(2))
            ->method('detach')
            ->with($this->callback(function ($callbackHandler) use ($callbackHandlers) {
                return in_array($callbackHandler, $callbackHandlers, true);
            }))
            ->will($this->returnValue(true));

        $this->listener->detach($eventManager);
        $this->assertEmpty($this->listener->getCallbacks());
    }
}
