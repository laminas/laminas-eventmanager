<?php

declare(strict_types=1);

namespace LaminasBench\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @Warmup(2)
 */
class SingleEventMultipleSharedListenerBench
{
    use BenchTrait;

    /** @var EventManager */
    private $events;

    public function __construct()
    {
        $identifiers  = $this->getIdentifierList();
        $sharedEvents = new SharedEventManager();
        for ($i = 0; $i < $this->numListeners; $i += 1) {
            $sharedEvents->attach($identifiers[0], 'dispatch', $this->generateCallback());
        }
        $this->events = new EventManager($sharedEvents, [$identifiers[0]]);
    }

    public function benchTrigger()
    {
        $this->events->trigger('dispatch');
    }
}
