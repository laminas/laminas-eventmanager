<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

use Laminas\EventManager\Exception;

class PrioritizedAggregateListenerProvider implements PrioritizedListenerProviderInterface
{
    /**
     * @var PrioritizedListenerProviderInterface[]
     */
    private $providers;

    public function __construct(array $providers)
    {
        $this->validateProviders($providers);
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     * @param string[] $identifiers Any identifiers to use when retrieving
     *     listeners from child providers.
     */
    public function getListenersForEvent(object $event, array $identifiers = []): iterable
    {
        yield from $this->iterateByPriority(
            $this->getListenersForEventByPriority($event, $identifiers)
        );
    }

    public function getListenersForEventByPriority($event, array $identifiers = []): array
    {
        $prioritizedListeners = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getListenersForEventByPriority($event, $identifiers) as $priority => $listeners) {
                $prioritizedListeners[$priority] = isset($prioritizedListeners[$priority])
                    ? array_merge($prioritizedListeners[$priority], $listeners)
                    : $listeners;
            }
        }

        return $prioritizedListeners;
    }

    /**
     * @throws Exception\InvalidArgumentException if any provider is not a
     *     PrioritizedListenerProviderInterface instance
     */
    private function validateProviders(array $providers): void
    {
        foreach ($providers as $index => $provider) {
            if (! $provider instanceof PrioritizedListenerProviderInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s requires all providers be instances of %s; received provider of type "%s" at index %d',
                    __CLASS__,
                    PrioritizedListenerProviderInterface::class,
                    gettype($provider),
                    $index
                ));
            }
        }
    }

    private function iterateByPriority(array $prioritizedListeners): iterable
    {
        krsort($prioritizedListeners);
        foreach ($prioritizedListeners as $listeners) {
            yield from $listeners;
        }
    }
}
