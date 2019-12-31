<?php

namespace LaminasBench\EventManager;

use Athletic\AthleticEvent;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;

class SingleEventSingleSharedListener extends AthleticEvent
{
    use TraitEventBench;

    private $sharedEvents;

    private $events;

    public function setUp()
    {
        $identifiers = $this->getIdentifierList();
        $this->sharedEvents = new SharedEventManager();
        $this->sharedEvents->attach($identifiers[0], 'dispatch', $this->generateCallback());
        $this->events = new EventManager($this->sharedEvents, [$identifiers[0]]);
    }

    /**
     * Trigger the dispatch event
     *
     * @iterations 5000
     */
    public function trigger()
    {
        $this->events->trigger('dispatch');
    }
}
