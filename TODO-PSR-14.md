# TODO for PSR-14 implementation

## 3.3.0 forwards-compatibility release

- [ ] `StoppableEventInterface` implementation
  - [x] Create a `StoppableEventInterface`
  - [x] Make `Event` implement it
  - [x] Deprecate `propagationIsStopped()` in both `EventInterface` and `Event`
    - [x] Have `Event::propagationIsStopped()` proxy to `Event::isPropagationStopped()`
  - [x] Modify `EventManager` internals to use the PSR-14 method if available
  - [ ] Mark `StoppableEventInterface` as deprecated
- [ ] Listener provider implementation
  - [x] Create a `ListenerProvider` subnamespace
  - [x] Create a `ListenerProviderInterface` shim
  - [x] Create a `PrioritizedListenerProvider` interface extending the
      `ListenerProviderInterface` and defining a
      `getListenersForEventByPriority($event, array $identifiers = []) : array<int, callable[]>` method.
  - [x] Create a `PrioritizedListenerAttachmentInterface`, defining:
    - [x] `attach($event, callable $listener, $priority = 1)` (where `$event`
      can be an object or string name)
    - [x] `detach(callable $listener, $event = null, $force = false)` (where `$event`
      can be an object or string name and `$force` is boolean)
    - [x] `attachWildcardListener(callable $listener, $priority = 1)`
      (`attach('*', $listener, $priority)` will proxy to this method)
    - [x] `detachWildcardListener(callable $listener, $force = false)`
      (`detach($listener, '*', $force)` will proxy to this method)
    - [x] `clearListeners($event)`
  - [x] Create a `PrioritizedListenerProvider` implementation of the above based
    on the internals of `EventManager`
    - [x] attachment/detachment
    - [x] getListenersForEvent should take into account event name if an EventInterface
    - [x] getListenersForEvent should also pull wildcard listeners
    - [x] getListenersForEvent should accept an optional second argument, an
      array of identifiers. This method will return all listeners in prioritized
      order.
    - [x] implement `getListenersForEventByPriority`
  - [x] Create a `PrioritizedIdentifierListenerProvider` that implements
      both the `PrioritizedListenerProvider` interface and the
      `SharedEventManagerInterface`
    - [x] implement `getListenersForEventByPriority`
    - [x] `SharedEventManager` will extend this class
    - [x] mark as deprecated (will not use this in v4)
  - [x] Create a `PrioritizedAggregateListenerProvider` implementation
    - [x] Accepts a list of `PrioritizedListenerProvider` instances
    - [x] `getListenersByEvent()` will loop through each, in order, calling the
      `getListenersForEventByPriority()` method of each, returning the
      aggregated listeners in priority order.
  - [x] Make `SharedEventManager` an extension of `PrioritizedIdentifierListenerProvider`
  - [x] Create `ListenerSubscriberInterface`
    - [x] `attach(PrioritizedListenerAttachmentInterface $provider, $priority = 1)`
    - [x] `detach(PrioritizedListenerAttachmentInterface $provider)`
  - [x] Create `AbstractListenerSubscriber` and/or `ListenerSubscriberTrait`
    - [x] define a default `detach()` implementation
  - [x] Create `LazyListenerSubscriber` based on `LazyListenerAggregate`
    - [x] Define an alternate LazyListener:
      - [x] `__construct(ContainerInterface $container, string $event = null, int $priority = 1)`
      - [x] implements functionality from both `LazyListener` and `LazyEventListener`, minus passing env to container
      - [x] without an event, can be attached to any provider
      - [x] with an event, can be attached to `LazyListenerSubscriber`
    - [x] Constructor aggregates `LazyListener` _instances_ only
      - [x] raises exception when `getEvent()` returns null
- [ ] Event Dispatcher implementation
  - [ ] Implement `PrioritizedListenerAttachmentInterface` (if BC)
  - [ ] Create a `PrioritizedListenerProvider` instance in the `EventManger`
    constructor, and have the various `attach()`, `detach()`, etc. methods
    proxy to it.
  - [ ] When triggering listeners, create a `PrioritizedAggregateListenerProvider`
    with the composed `PrioritizedListenerProvider` and `SharedListenerProvider` /
    `PrioritizedIdentifierListenerProvider` implementations, in that order.
  - [ ] Replace logic of `triggerListeners()` to just call
    `getListenersForEvent()` on the provider. It can continue to aggregate the
    responses in a `ResponseCollection`
  - [ ] `triggerListeners()` no longer needs to type-hint its first argument
  - [ ] Create a `dispatch()` method
    - [ ] Method will act like `triggerEvent()`, except
    - [ ] it will return the event itself
    - [ ] it will need to validate that it received an object before calling
      `triggerListeners`
- [ ] Additional utilities
  - [ ] `EventDispatchingInterface` with a `getEventDispatcher()` method
  - [ ] Alternate dispatcher implementation, `EventDispatcher`
    - [ ] Should accept a listener provider interface to its constructor
    - [ ] Should implement `EventDispatcherInterface` via duck-typing: it will
        implement a `dispatch()` method only
- [ ] Deprecations
  - [ ] `EventInterface`
  - [ ] `EventManager`
  - [ ] `EventManagerInterface`
  - [ ] `EventManagerAwareInterface`
  - [ ] `EventManagerAwareTrait`
  - [ ] `EventsCapableInterface` (point people to `EventDispatchingInterface`)
  - [ ] `SharedEventManager`
  - [ ] `SharedEventManagerInterface`
  - [ ] `SharedEventsCapableInterface`
  - [ ] `ListenerAggregateInterface` (point people to the `PrioritizedListenerAttachmentInterface`)
  - [ ] `ListenerAggregateTrait` (point people to `ListenerSubscriberTrait`)
  - [ ] `AbstractListenerAggregate` (point people to `AbstractListenerSubscriber` and/or `ListenerSubscriberTrait`)
  - [ ] `ResponseCollection` (tell people to aggregate state/results in the event itself)
  - [ ] `LazyListener` (point people to `ListenerProvider\LazyListener`)
  - [ ] `LazyEventListener` (point people to `ListenerProvider\LazyListener`)
  - [ ] `LazyListenerAggregate` (point people to `ListenerProvider\LazyListenerSubscriber`)
  - [ ] `FilterChain` and `Filter` subnamespace (this should be done in a separate component)

## 4.0.0 full release

- [ ] Removals
  - [ ] `EventInterface`
  - [ ] `EventManager`
  - [ ] `EventManagerInterface`
  - [ ] `EventManagerAwareInterface`
  - [ ] `EventManagerAwareTrait`
  - [ ] `EventsCapableInterface`
  - [ ] `SharedEventManager`
  - [ ] `SharedEventManagerInterface`
  - [ ] `SharedEventsCapableInterface`
  - [ ] `ListenerAggregateInterface`
  - [ ] `ListenerAggregateTrait`
  - [ ] `AbstractListenerAggregate`
  - [ ] `ResponseCollection`
  - [ ] `LazyListener`
  - [ ] `LazyEventListener`
  - [ ] `LazyListenerAggregate`
  - [ ] `FilterChain` and `Filter` subnamespace
  - [ ] `StoppableEventInterface` (will use PSR-14 version)
  - [ ] `ListenerProviderInterface` (will use PSR-14 version)
  - [ ] `PrioritizedIdentifierListenerProvider`
- Changes
  - [ ] `PrioritizedListenerAttachmentInterface` (and implementations)
    - [ ] extend PSR-14 `ListenerProviderInterface`
    - [ ] add `string` typehint to `$event` in `attach()` and `detach()`
    - [ ] add `bool` typehint to `$force` argument of `detach()`
  - [ ] `PrioritizedListenerProvider` interface (and implementations)
    - [ ] Fulfill PSR-14 `ListenerProviderInterface`
    - [ ] remove `$identifiers` argument to getListenersForEventByPriority and getListenersForEvent
    - [ ] add `object` typehint to `getListenersForEventByPriority`
  - [ ] `EventDispatcher`
    - [ ] implement PSR-14 `EventDispatcherInterface`

## Concerns

### MVC

Currently, the MVC relies heavily on:

- event names (vs types)
- event targets
- event params
- `stopPropagation($flag)` (vs custom stop conditions in events)
- `triggerEventUntil()` (vs custom stop conditions in events)

We would need to draw attention to usage of methods that are not specific to an
event implementation, and recommend usage of other methods where available.
(We would likely keep the params implementation, however, to allow passing
messages via the event instance(s).)

Additionally, we will need to have some sort of event hierarchy:

- a base MVC event from which all others derive. This will be necessary to
  ensure that existing code continues to work.
- a BootstrapEvent
- a RouteEvent
- a DispatchEvent
  - a DispatchControllerEvent
- a DispatchErrorEvent
  - Potentially broken into a RouteUnmatchedEvent, DispatchExceptionEvent,
    MiddlewareExceptionEvent, ControllerNotFoundEvent, InvalidControllerEvent,
    and InvalidMiddlewareEvent
- a RenderEvent
- a RenderErrorEvent
- a FinishEvent
- a SendResponseEvent (this one is not an MvcEvent, however)

The event names associated with each would be based on existing event names,
allowing the ability to attach using legacy names OR the class name.

We can allow using `stopPropagation()`, but have it trigger a deprecation
notice, asking users to use more specific methods of the event to stop
propagation, or, in the case of errors, raising exceptions.

- `setError()` would cause `isPropagationStopped()` to return true.
- A new method, `setFinalResponse()` would both set the response instance, as
  well as cause `isPropagationStopped()` to return true.
- The `RouteEvent` would also halt propagation when `setRouteResult()` is
  called.

Internally, we will also stop using the `*Until()` methods, and instead rely on
the events to handle this for us. If we need a return value, we will instead
pull it from the event on completion.