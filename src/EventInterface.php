<?php

namespace Laminas\EventManager;

use ArrayAccess;

/**
 * Representation of an event
 * 
 * @phpstan-template TTarget of object|string|null
 * @phpstan-template TParams of \ArrayAccess|array
 */
interface EventInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     * @phpstan-return TTarget
     */
    public function getTarget();

    /**
     * Get parameters passed to the event
     *
     * @return array|ArrayAccess
     * @phpstan-return TParams
     */
    public function getParams();

    /**
     * Get a single parameter by name
     *
     * @param  string $name
     * @param  mixed $default Default value to return if parameter does not exist
     * @return mixed
     * @phpstan-return value-of<TParams>
     */
    public function getParam($name, $default = null);

    /**
     * Set the event name
     *
     * @param  string $name
     * @return void
     */
    public function setName($name);

    /**
     * Set the event target/context
     *
     * @param  null|string|object $target
     * @phpstan-param TTarget $target
     * @return void
     */
    public function setTarget($target);

    /**
     * Set event parameters
     *
     * @param  array|ArrayAccess $params
     * @phpstan-param TParams $params
     * @return void
     */
    public function setParams($params);

    /**
     * Set a single parameter by key
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setParam($name, $value);

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events
     *
     * @param  bool $flag
     * @return void
     */
    public function stopPropagation($flag = true);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function propagationIsStopped();
}
