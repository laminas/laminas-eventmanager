<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventManagerAwareTrait;

final class MockEventManagerAwareTrait
{
    use EventManagerAwareTrait;

    private string $eventIdentifier           = 'foo.bar';
    private bool $defaultEventListenersCalled = false;

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
