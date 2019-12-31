<?php

namespace LaminasBench\EventManager;

use Athletic\AthleticEvent;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;

class SingleEventMultipleSharedListener extends AthleticEvent
{
    use TraitEventBench;

    private $sharedEvents;

    private $events;

    public function setUp()
    {
        $identifiers = $this->getIdentifierList();
        $this->sharedEvents = new SharedEventManager();
        for ($i = 0; $i < $this->numListeners; $i += 1) {
            $this->sharedEvents->attach($identifiers[0], 'dispatch', $this->generateCallback());
        }
        $this->events = new EventManager();
        $this->events->setSharedManager($this->sharedEvents);
        $this->events->setIdentifiers([$identifiers[0]]);
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
