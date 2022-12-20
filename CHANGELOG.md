# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.9.3 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.9.2 - 2022-12-20


-----

### Release Notes for [3.9.2](https://github.com/laminas/laminas-eventmanager/milestone/18)

3.9.x bugfix release (patch)

### 3.9.2

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug,Documentation

 - [38: Updates workflow file for docs build to use deploy token instead of legacy docs deploy key](https://github.com/laminas/laminas-eventmanager/pull/38) thanks to @froschdesign

## 3.9.1 - 2022-12-20


-----

### Release Notes for [3.9.1](https://github.com/laminas/laminas-eventmanager/milestone/16)

3.9.x bugfix release (patch)

### 3.9.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug,Documentation

 - [36: Adds missing description for usage in a laminas-mvc based application](https://github.com/laminas/laminas-eventmanager/pull/36) thanks to @froschdesign

## 3.9.0 - 2022-12-10


-----

### Release Notes for [3.9.0](https://github.com/laminas/laminas-eventmanager/milestone/15)

Feature release (minor)

### 3.9.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [32: Add generic annotations to `EventInterface`](https://github.com/laminas/laminas-eventmanager/pull/32) thanks to @villermen

## 3.8.0 - 2022-12-03


-----

### Release Notes for [3.8.0](https://github.com/laminas/laminas-eventmanager/milestone/13)

Feature release (minor)

### 3.8.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### renovate

 - [34: Update dependencies, document `FilterIterator` generic types](https://github.com/laminas/laminas-eventmanager/pull/34) thanks to @renovate[bot]

## 3.7.0 - 2022-12-01


-----

### Release Notes for [3.7.0](https://github.com/laminas/laminas-eventmanager/milestone/11)

Feature release (minor)

### 3.7.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Documentation,Enhancement,renovate

 - [33: Update dependency vimeo/psalm to v5](https://github.com/laminas/laminas-eventmanager/pull/33) thanks to @renovate[bot]

## 3.6.0 - 2022-10-11


-----

### Release Notes for [3.6.0](https://github.com/laminas/laminas-eventmanager/milestone/9)

Feature release (minor)

### 3.6.0

- Total issues resolved: **1**
- Total pull requests resolved: **4**
- Total contributors: **3**

#### Enhancement

 - [31: Add support for PHP 8.2, Remove support for PHP 7.4](https://github.com/laminas/laminas-eventmanager/pull/31) thanks to @gsteel
 - [29: Setup Psalm](https://github.com/laminas/laminas-eventmanager/pull/29) thanks to @gsteel and @boesing

#### Awaiting Maintainer Response,renovate

 - [28: chore(deps): update dependency laminas/laminas-coding-standard to ~2.3.0 - autoclosed](https://github.com/laminas/laminas-eventmanager/pull/28) thanks to @renovate[bot]

#### renovate

 - [26: Configure Renovate](https://github.com/laminas/laminas-eventmanager/pull/26) thanks to @renovate[bot]

## 3.5.0 - 2022-04-06


-----

### Release Notes for [3.5.0](https://github.com/laminas/laminas-eventmanager/milestone/7)

### Changed

The `LazyListener`, `LazyEventListener`, and `LazyListenerAggregate` have type-widened their `$container` argument to the official [PSR-11](https://www.php-fig.org/psr/psr-11/) `ContainerInterface` type. Since they previously hinted against the container-interop version 1.2 or greater, and that version extended the PSR-11 interface, the change is backwards compatible.

If you were extending these classes, we recommend updating your definitions to reference PSR-11's types instead.

### 3.5.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Enhancement

 - [25: Update to PSR-11 for lazy listener support](https://github.com/laminas/laminas-eventmanager/pull/25) thanks to @weierophinney
 - [24: Prepare for Renovate with reusable workflows](https://github.com/laminas/laminas-eventmanager/pull/24) thanks to @ghostwriter

## 3.4.0 - 2021-09-07


-----

### Release Notes for [3.4.0](https://github.com/laminas/laminas-eventmanager/milestone/2)



### 3.4.0

- Total issues resolved: **0**
- Total pull requests resolved: **5**
- Total contributors: **2**

#### Enhancement

 - [22: Provide PHP 8.1 support](https://github.com/laminas/laminas-eventmanager/pull/22) thanks to @weierophinney
 - [15: Refactor unit-tests action to automate test matrix](https://github.com/laminas/laminas-eventmanager/pull/15) thanks to @weierophinney
 - [14: Improve CI workflow](https://github.com/laminas/laminas-eventmanager/pull/14) thanks to @weierophinney
 - [13: Switch from Travis-Ci to GitHub Actions](https://github.com/laminas/laminas-eventmanager/pull/13) thanks to @weierophinney

#### Duplicate,Enhancement

 - [18: Remove file headers](https://github.com/laminas/laminas-eventmanager/pull/18) thanks to @ghostwriter

## 3.3.1 - 2021-03-08

### Release Notes for [3.3.1](https://github.com/laminas/laminas-eventmanager/milestone/3)

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug,Documentation

 - [16: &#91;Doc Tutorial&#93; Add missing , $priority = 1 parameter in example LogEvents::attach()](https://github.com/laminas/laminas-eventmanager/pull/16) thanks to @samsonasik

## 3.3.0 - 2020-08-25

### Added

- [#10](https://github.com/laminas/laminas-eventmanager/pull/10) adds support for the upcoming PHP 8.0 release.

- [zendframework/zend-eventmanager#72](https://github.com/zendframework/zend-eventmanager/pull/72) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#10](https://github.com/laminas/laminas-eventmanager/pull/10) removes support for PHP versions prior to PHP 7.3.

### Fixed

- Nothing.

## 3.2.1 - 2018-04-25

### Added

- [zendframework/zend-eventmanager#66](https://github.com/zendframework/zend-eventmanager/pull/66) adds support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.2.0 - 2017-07-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-eventmanager#47](https://github.com/zendframework/zend-eventmanager/pull/47) removes
  support for PHP 5.5 and HHVM.

### Fixed

- Nothing.

## 3.1.0 - 2016-12-19

### Added

- [zendframework/zend-eventmanager#26](https://github.com/zendframework/zend-eventmanager/pull/26) publishes
  the documentation to https://docs.laminas.dev/laminas-eventmanager/

### Changes

- [zendframework/zend-eventmanager#17](https://github.com/zendframework/zend-eventmanager/pull/17) makes a
  number of internal changes to how listeners are stored in order to improve
  performance, by as much as 10% in the scenario used in the MVC layer.
  
  Additionally, it optimizes when the target and event arguments are injected
  into an event, eliminating that step entirely when either is unavailable.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.1 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-eventmanager#24](https://github.com/zendframework/zend-eventmanager/pull/24) updates the
  laminas-stdlib dependency to `^2.7.3 || ^3.0`, allowing either major version.

## 3.0.0 - 2016-01-12

### Added

- [Migration documentation](doc/book/migration/) was added.
- [Automated benchmarks](benchmarks/) were added.
- `EventManager::__construct()` now accepts an optional
  `SharedEventManagerInterface` instance as the first argument, and an optional
  array of identifiers as the second. As identifiers have no meaning without a
  shared manager present, they are secondary to providing the shared manager.
- `EventManagerInterface::trigger()` changes its signature to
  `trigger($eventName, $target = null, $argv = [])`; each argument has exactly
  one possible meaning; the `$eventName` can only be a string event name. The
  fourth `$callback` argument is removed.
- `EventManagerInterface::triggerUntil()` changes its signature to
  `triggerUntil(callable $callback, $eventName, $target = null, $argv = null)`.
  Each argument has exactly one meaning.
- `EventManagerInterface` adds two new methods for triggering provided
  `EventInterface` arguments: `triggerEvent(EventInterface $event)` and
  `triggerEventUntil(callable $callback, EventInterface $event)`.
- `EventManagerInterface::attach()` and `detach()` change their signatures to
  `attach($eventName, callable $listener, $priority = 1)` and `detach(callable
  $listener, $eventName = null)`, respectively. Note that `$eventName` can now
  only be a string event name, not an array or `Traversable`.
- `EventManagerInterface::setIdentifiers()` and `addIdentifiers()` change their
  signatures to each only accept an *array* of identifiers.
- `SharedEventManagerInterface::getListeners()` changes signature to
  `getListeners(array $identifiers, $eventName)` and now guarantees return of an
  array. Note that the second argument is now *required*.
- `SharedEventManagerInterface::attach()` changes signature to
  `attach($identifier, $eventName, callable $listener, $priority = 1)`. The
  `$identifier` and `$eventName` **must** be strings.
- `SharedEventManagerInterface::detach()` changes signature to `detach(callable
  $listener, $identifier = null, $eventName = null)`; `$identifier` and
  `$eventName` **must** be strings if passed.
- `ListenerAggregateInterface::attach()` adds an optional `$priority = 1`
  argument. This was used already in v2, but not dictated by the interface.
- `FilterInterface::attach()` and `detach()` have changed signature to
  `attach(callable $callback)` and `detach(callable $ilter)`, respectively.
- `LazyListener` allows wrapping:
  - fetching a listener service from a container-interop container, and
  - invoking a designated listener method with the provided event.
- `LazyEventListener` extends `LazyListener`, and provides metadata for
  discovering the intended event name and priority at which to attach the lazy
  listener; these are consumed by:
- `LazyListenerAggregate`, which, provided a list of `LazyEventListeners` and/or
  definitions to use to create them, acts as an aggregate for attaching a number
  of such listeners at once.
- [zendframework/zend-eventmanager#20](https://github.com/zendframework/zend-eventmanager/pull/20) updates the
  trait `Laminas\EventManager\Test\EventListenerIntrospectionTrait` so that the
  implementation will work with the v3 changes; the tests written for v2
  continue to pass, allowing this trait to be used to provide compatibility
  testing between v2 and v3.

### Deprecated

- Nothing.

### Removed

- `GlobalEventManager` and `StaticEventManager` are removed (with prejudice!).
- `ProvidesEvents`, which was previously deprecated, is removed.
- `EventManagerInterface::setSharedManager()` is removed. Shared managers are
  now expected to be injected during instantiation.
- `EventManagerInterface::getEvents()` and `getListeners()` are removed; they
  had now purpose within the implementation.
- `EventManagerInterface::setEventClass()` was renamed to `setEventPrototype()`,
  which now expects an `EventInterface` instance. That instance will be cloned
  whenever a new event is created.
- `EventManagerInterface::attachAggregate()` and `detachAggregate()` are
  removed. Users should use the `attach()` and `detach()` methods of the
  aggregates themselves.
- `SharedEventAggregateAwareInterface` and `SharedListenerAggregateInterface`
  are removed. This was an undocumented and largely unused feature.
- `SharedEventManagerAwareInterface` is removed. A new interface,
  `SharedEventsCapableInterface` defines the `getSharedManager()` method from
  the interface, and `EventManagerInterface` extends that new interface.
- `SharedEventManagerInterface::getEvents()` is removed, as it had no purpose in
  the implementation.
- `ResponseCollection::setStopped()` no longer implements a fluent interface.

### Fixed

- `FilterIterator::insert()` has been modified to raise an exception if the value provided is not a callable.

## 2.6.2 - 2016-01-12

### Added

- [zendframework/zend-eventmanager#19](https://github.com/zendframework/zend-eventmanager/pull/19) adds a new
  trait, `Laminas\EventManager\Test\EventListenerIntrospectionTrait`, intended for
  composition in unit tests. It provides a number of methods that can be used
  to retrieve listeners with or without associated priority, and the assertion
  `assertListenerAtPriority(callable $listener, $priority, $event, EventManager $events, $message = '')`,
  which can be used for testing that a listener was registered at the specified
  priority with the specified event.

  The features in this patch are intended to facilitate testing against both
  version 2 and version 3 of laminas-eventmanager, as it provides a consistent API
  for retrieving lists of events and listeners between the two versions.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2015-09-29

### Added

- Added `Laminas\EventManager\SharedEventsCapableInterface`. This interface will
  largely replace `Laminas\EventManager\SharedEventManagerAwareInterface` in
  version 3, and the latter was updated to extend it.
- Added `EventManager::triggerEvent(EventInterface $event)` as a
  forwards-compatibility feature.
- Add `EventManager::triggerEventUntil(callable $callback, EventIterface $event)`
  as a forwards-compatibility feature.
- Adds [Athletic](https://github.com/polyfractal/athletic) benchmarks to aid in
  gauging performanc impact of changes; these are a development change only.

### Deprecated

- Marked `GlobalEventManager` as deprecated; this class will be removed in
  version 3.
- Marked `StaticEventManager` as deprecated; this class will be removed in
  version 3.
- Marked `SharedListenerAggregateInterface` as deprecated; this interface will
  be removed in version 3.
- Marked `SharedEventAggregateAwareInterface` as deprecated; this interface will
  be removed in version 3.
- Marked `SharedEventManagerAwareInterface` as deprecated; this interface will
  be removed in version 3.
- Marked `EventManager::setSharedManager()` as deprecated; this method will be
  removed in version 3.
- Marked `EventManager::unsetSharedManager()` as deprecated; this method will be
  removed in version 3.
- Marked `EventManagerInterface::` and `EventManager::getEvents()` as
  deprecated; this method will be removed in version 3.
- Marked `EventManagerInterface::` and `EventManager::getListeners()` as
  deprecated; this method will be removed in version 3.
- Marked `EventManagerInterface::` and `Eventmanager::setEventClass()` as
  deprecated; this method is renamed to `setEventPrototype(EventInterface $event)`
  in version 3.
- Marked `EventManagerInterface::` and `EventManager::attachAggregate()` as
  deprecated; this method will be removed in version 3.
- Marked `EventManagerInterface::` and `EventManager::detachAggregate()` as
  deprecated; this method will be removed in version 3.
- Marked `SharedEventManagerInterface::` and `SharedEventManager::getEvents()`
  as deprecated; this method will be removed in version 3.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-07-16

### Added

- [zendframework/zend-eventmanager#5](https://github.com/zendframework/zend-eventmanager/pull/5) adds a number
  of unit tests to improve test coverage, and thus maintainability and
  stability.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-eventmanager#3](https://github.com/zendframework/zend-eventmanager/pull/3) removes some
  PHP 5.3- and 5.4-isms (such as marking Traits as requiring 5.4, and closing
  over a copy of `$this`) from the test suite.

### Fixed

- [zendframework/zend-eventmanager#5](https://github.com/zendframework/zend-eventmanager/pull/5) fixes a bug in
  `FilterIterator` that occurs when attempting to extract from an empty heap.
