<?php

declare(strict_types=1);

namespace LaminasBench\EventManager;

use Laminas\EventManager\EventManager;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @Warmup(2)
 */
class SingleEventMultipleListenerBench
{
    use BenchTrait;

    /** @var EventManager */
    private $events;

    public function __construct()
    {
        $this->events = new EventManager();
        for ($i = 0; $i < $this->numListeners; $i++) {
            $this->events->attach('dispatch', $this->generateCallback());
        }
    }

    public function benchTrigger()
    {
        $this->events->trigger('dispatch');
    }
}
