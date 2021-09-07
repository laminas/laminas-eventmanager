<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerAwareTrait;

/**
 * @group      Laminas_EventManager
 */
class MockEventManagerAwareTrait
{
    use EventManagerAwareTrait;

    /** @var string */
    protected $eventIdentifier = 'foo.bar';

    /** @var bool */
    protected $defaultEventListenersCalled = false;

    public function getEventIdentifier(): string
    {
        return $this->eventIdentifier;
    }

    public function setEventIdentifier(string $eventIdentifier): self
    {
        $this->eventIdentifier = $eventIdentifier;
        return $this;
    }

    public function attachDefaultListeners(): void
    {
        $this->defaultEventListenersCalled = true;
    }

    public function defaultEventListenersCalled(): bool
    {
        return $this->defaultEventListenersCalled;
    }
}
