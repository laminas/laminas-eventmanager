<?php

namespace LaminasBench\EventManager;

use Athletic\AthleticEvent;
use Laminas\EventManager\EventManager;

class MultipleEventLocalListener extends AthleticEvent
{
    use TraitEventBench;

    private $eventsToTrigger;

    public function setUp()
    {
        $this->events = new EventManager();

        $this->eventsToTrigger = array_filter($this->getEventList(), function ($value) {
            return ($value !== '*');
        });
    }

    /**
     * Attach and trigger the event list
     *
     * @iterations 5000
     */
    public function trigger()
    {
        foreach ($this->eventsToTrigger as $event) {
            $this->events->attach($event, $this->generateCallback());
            $this->events->trigger($event);
        }
    }
}
