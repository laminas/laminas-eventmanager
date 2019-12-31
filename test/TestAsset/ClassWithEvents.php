<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;

/**
 * @category   Laminas
 * @package    Laminas_EventManager
 * @subpackage UnitTests
 * @group      Laminas_EventManager
 */
class ClassWithEvents
{
    protected $events;

    public function getEventManager(EventManagerInterface $events = null)
    {
        if (null !== $events) {
            $this->events = $events;
        }
        if (null === $this->events) {
            $this->events = new EventManager(__CLASS__);
        }
        return $this->events;
    }

    public function foo()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array());
    }
}
