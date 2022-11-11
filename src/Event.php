<?php

namespace Laminas\EventManager;

use ArrayAccess;

use function gettype;
use function is_array;
use function is_object;
use function sprintf;

/**
 * Representation of an event
 *
 * Encapsulates the target context and parameters passed, and provides some
 * behavior for interacting with the event manager.
 *
 * @template TTarget of object|string|null
 * @template TParams of \ArrayAccess|array
 * @implements EventInterface<TTarget, TParams>
 */
class Event implements EventInterface
{
    /** @var string Event name */
    protected $name;

    /**
     * @var string|object|null The event target
     * @psalm-var TTarget
     */
    protected $target;

    /**
     * @var array|ArrayAccess|object The event parameters
     * @psalm-var TParams
     */
    protected $params = [];

    /** @var bool Whether or not to stop propagation */
    protected $stopPropagation = false;

    /**
     *
     * Constructor
     *
     * Accept a target and its parameters.
     *
     * @param  string $name Event name
     * @param  string|object $target
     * @param  array|ArrayAccess $params
     *
     * @template NewTTarget of object|string|null
     * @template NewTParams of \ArrayAccess|array
     * @psalm-param NewTTarget|null $target
     * @psalm-param NewTParams|null $params
     * @psalm-this-out self<TTarget|NewTTarget>
     * @psalm-this-out self<TParams|NewTParams>
     */
    public function __construct($name = null, $target = null, $params = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $target) {
            $this->setTarget($target);
        }

        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the event target
     *
     * This may be either an object, or the name of a static method.
     *
     * @return string|object|null
     * @psalm-return TTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set parameters
     *
     * Overwrites parameters
     *
     * @param  array|ArrayAccess|object $params
     * @template NewTParams
     * @psalm-param NewTParams $params
     * @psalm-this-out self<TParams|NewTParams>
     * @throws Exception\InvalidArgumentException
     */
    public function setParams($params)
    {
        if (! is_array($params) && ! is_object($params)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Event parameters must be an array or object; received "%s"', gettype($params))
            );
        }

        $this->params = $params;
    }

    /**
     * Get all parameters
     *
     * @return array|object|ArrayAccess
     * @psalm-return TParams
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get an individual parameter
     *
     * If the parameter does not exist, the $default value will be returned.
     *
     * @param  string|int $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        // Check in params that are arrays or implement array access
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            if (! isset($this->params[$name])) {
                return $default;
            }

            return $this->params[$name];
        }

        // Check in normal objects
        if (! isset($this->params->{$name})) {
            return $default;
        }
        return $this->params->{$name};
    }

    /**
     * Set the event name
     *
     * @param  string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Set the event target/context
     *
     * @param  null|string|object $target
     * @template NewTTarget
     * @psalm-param NewTTarget $target
     * @psalm-this-out self<TTarget|NewTTarget>
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Set an individual parameter to a value
     *
     * @param  string|int $name
     * @param  mixed $value
     */
    public function setParam($name, $value)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            // Arrays or objects implementing array access
            $this->params[$name] = $value;
            return;
        }

        // Objects
        $this->params->{$name} = $value;
    }

    /**
     * Stop further event propagation
     *
     * @param  bool $flag
     */
    public function stopPropagation($flag = true)
    {
        $this->stopPropagation = (bool) $flag;
    }

    /**
     * Is propagation stopped?
     *
     * @return bool
     */
    public function propagationIsStopped()
    {
        return $this->stopPropagation;
    }
}
