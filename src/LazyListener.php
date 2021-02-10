<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

use Interop\Container\ContainerInterface;

use function is_string;
use function method_exists;

/**
 * Lazy listener instance.
 *
 * Used to allow lazy creation of listeners via a dependency injection
 * container.
 *
 * Lazy listener definitions have the following members:
 *
 * - listener: the service name of the listener to use.
 * - method: the method name of the listener to invoke for the specified event.
 *
 * If desired, you can pass $env at instantiation; this will be passed to the
 * container's `build()` method, if it has one, when creating the listener
 * instance.
 *
 * Pass instances directly to the event manager's `attach()` method as the
 * listener argument.
 *
 * @deprecated since 3.4.0. This class will be removed in version 4.0.0, in
 *     favor of the ListenerProvider\LazyListener implementation.
 */
class LazyListener
{
    /**
     * @var ContainerInterface Container from which to pull listener.
     */
    private $container;

    /**
     * @var array Variables/options to use during service creation, if any.
     */
    private $env;

    /**
     * @var callable Marshaled listener callback.
     */
    private $listener;

    /**
     * @var string Method name to invoke on listener.
     */
    private $method;

    /**
     * @var string Service name of listener.
     */
    private $service;

    /**
     * @param array $definition
     * @param ContainerInterface $container
     * @param array $env
     */
    public function __construct(array $definition, ContainerInterface $container, array $env = [])
    {
        if ((! isset($definition['listener'])
            || ! is_string($definition['listener'])
            || empty($definition['listener']))
        ) {
            throw new Exception\InvalidArgumentException(
                'Lazy listener definition is missing a valid "listener" member; cannot create LazyListener'
            );
        }

        if ((! isset($definition['method'])
            || ! is_string($definition['method'])
            || empty($definition['method']))
        ) {
            throw new Exception\InvalidArgumentException(
                'Lazy listener definition is missing a valid "method" member; cannot create LazyListener'
            );
        }

        $this->service   = $definition['listener'];
        $this->method    = $definition['method'];
        $this->container = $container;
        $this->env       = $env;
    }

    /**
     * Use the listener as an invokable, allowing direct attachment to an event manager.
     *
     * @param EventInterface $event
     * @return callable
     */
    public function __invoke(EventInterface $event)
    {
        $listener = $this->fetchListener();
        $method   = $this->method;
        return $listener->{$method}($event);
    }

    /**
     * @return callable
     */
    private function fetchListener()
    {
        if ($this->listener) {
            return $this->listener;
        }

        // In the future, typehint against Laminas\ServiceManager\ServiceLocatorInterface,
        // which defines this message starting in v3.
        if (method_exists($this->container, 'build') && ! empty($this->env)) {
            $this->listener = $this->container->build($this->service, $this->env);
            return $this->listener;
        }

        $this->listener = $this->container->get($this->service);
        return $this->listener;
    }
}
