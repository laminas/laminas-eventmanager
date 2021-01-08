<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\ListenerProvider;

use Laminas\EventManager\Exception;
use Psr\Container\ContainerInterface;

/**
 * Lazy listener instance.
 *
 * Used to allow lazy creation of listeners via a dependency injection
 * container.
 */
class LazyListener
{
    /**
     * @var ContainerInterface Container from which to pull listener.
     */
    private $container;

    /**
     * @var null|string Event name to which to attach; for use with
     *     ListenerSubscriberInterface instances.
     */
    private $event;

    /**
     * @var object Service pulled from container
     */
    private $listener;

    /**
     * @var string Method name to invoke on listener.
     */
    private $method;

    /**
     * @var null|int Priority at which to attach; for use with
     *     ListenerSubscriberInterface instances.
     */
    private $priority;

    /**
     * @var string Service name of listener.
     */
    private $service;

    /**
     * @param ContainerInterface $container Container from which to pull
     *     listener service
     * @param string $listener Name of listener service to retrive from
     *     container
     * @param string $method Name of method on listener service to use
     *     when calling listener; defaults to __invoke.
     * @param null|string $event Name of event to which to attach; for use
     *     with ListenerSubscriberInterface instances. In that scenario, null
     *     indicates it should attach to any event.
     * @param null|int $priority Priority at which to attach; for use with
     *     ListenerSubscriberInterface instances. In that scenario, null indicates
     *     that the default priority should be used.
     * @throws Exception\InvalidArgumentException for invalid $listener arguments
     * @throws Exception\InvalidArgumentException for invalid $method arguments
     * @throws Exception\InvalidArgumentException for invalid $event arguments
     */
    public function __construct(
        ContainerInterface $container,
        string $listener,
        string $method = '__invoke',
        ?string $event = null,
        ?int $priority = null
    ) {
        if (empty($listener)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires a non-empty string $listener argument'
                . ' representing a service name; received %s',
                __CLASS__,
                gettype($listener)
            ));
        }

        if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $method)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires a valid string $method argument; received %s',
                __CLASS__,
                is_string($method) ? sprintf('"%s"', $method) : gettype($method)
            ));
        }

        if (null !== $event && empty($event)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires a null or non-empty string $event argument; received %s',
                __CLASS__,
                is_string($event) ? sprintf('"%s"', $event) : gettype($event)
            ));
        }

        $this->container = $container;
        $this->service   = $listener;
        $this->method    = $method;
        $this->event     = $event;
        $this->priority  = $priority;
    }

    /**
     * Use the listener as an invokable, allowing direct attachment to an event manager.
     */
    public function __invoke(object $event): void
    {
        $listener = $this->fetchListener();
        $method   = $this->method;
        $listener->{$method}($event);
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * Return the priority, or, if not set, the default provided.
     */
    public function getPriority(int $default = 1): int
    {
        return null !== $this->priority ? (int) $this->priority : (int) $default;
    }

    /**
     * @return callable
     */
    private function fetchListener(): callable
    {
        if ($this->listener) {
            return $this->listener;
        }

        $this->listener = $this->container->get($this->service);

        return $this->listener;
    }
}
