<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use LaminasTest\EventManager\TestAsset\MockListenerAggregateTrait;

class ListenerAggregateTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Laminas\EventManager\ListenerAggregateTrait::detach
     */
    public function testDetach()
    {
        $listener              = new MockListenerAggregateTrait();
        $eventManager          = $this->getMock('Laminas\\EventManager\\EventManagerInterface');
        $unrelatedEventManager = $this->getMock('Laminas\\EventManager\\EventManagerInterface');
        $callbackHandlers      = [];
        $test                  = $this;

        $eventManager
            ->expects($this->exactly(2))
            ->method('attach')
            ->will($this->returnCallback(function () use (&$callbackHandlers, $test) {
                return $callbackHandlers[] = $test->getMock('Laminas\\Stdlib\\CallbackHandler', [], [], '', false);
            }));

        $listener->attach($eventManager);
        $this->assertSame($callbackHandlers, $listener->getCallbacks());

        $listener->detach($unrelatedEventManager);

        $this->assertSame($callbackHandlers, $listener->getCallbacks());

        $eventManager
            ->expects($this->exactly(2))
            ->method('detach')
            ->with($this->callback(function ($callbackHandler) use ($callbackHandlers) {
                return in_array($callbackHandler, $callbackHandlers, true);
            }))
            ->will($this->returnValue(true));

        $listener->detach($eventManager);
        $this->assertEmpty($listener->getCallbacks());
    }
}
