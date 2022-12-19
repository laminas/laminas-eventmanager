# Usage in a laminas-mvc Application

The following example shows _one_ potential use case of laminas-eventmanager within
a laminas-mvc based application.
The example creates a listener for logging errors of an application with [laminas-log](https://docs.laminas.dev/laminas-log/).

Before starting, make sure laminas-log is [installed and configured](https://docs.laminas.dev/laminas-log/installation/).

laminas-eventmanager is already present in laminas-mvc based applications as it is an event-driven MVC layer based on the event manager.

## Create Listener

Create a listener as class using [a listener aggregate](../aggregates.md) and add the logger via constructor injection to create a valid object with all required dependencies, e.g. `module/Application/src/Listener/ErrorListener.php`:

```php
namespace Application\Listener;

use Exception;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Log\Logger;
use Laminas\Mvc\MvcEvent;

final class ErrorListener extends AbstractListenerAggregate
{
    public function __construct(
        private readonly Logger $logger
    ) {}

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            [$this, 'onDispatchError']
        );
    }

    public function onDispatchError(MvcEvent $event): void
    {
        /** @var Exception|null $exception */
        $exception = $event->getParam('exception');
        if ($exception) {
            $this->logger->crit('Error: ' . $exception->getMessage());
        } else {
            $this->logger->crit('Error: ' . $event->getError());
        }
    }
}
```

The listener aggregate was chosen because [it can listen to even more events](../aggregates.md#recommendations) due to the possible extension.

NOTE: **More Events**
All events that can be triggered are listed and explained in the [documentation of laminas-mvc](https://docs.laminas.dev/laminas-mvc/mvc-event/).

## Register Listener

To register a listener in a laminas-mvc based application use the application or module configuration, such as `config/autload/global.php` or `module/Application/config/module.config.php`, and the configuration key `listeners`.

The current example uses the module configuration, e.g. `module/Application/config/module.config.php`:

<pre class="language-php" data-line="4-6"><code>
namespace Application;

return [
    'listeners' => [
        Listener\ErrorListener::class,
    ],
    // …
];
</code></pre>

All listeners registered in this way are fetched from the application service container.
This means the listeners must be registered for the application service container.

To register the listener for the application service container, extend the configuration of the module.
Add the following lines to the module configuration file, e.g. `module/Application/config/module.config.php`:

<pre class="language-php" data-line="3,8"><code>
namespace Application;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    'service_manager' => [
        'factories' => [
            Listener\ErrorListener::class => ReflectionBasedAbstractFactory::class,
        ],
    ],
    // …
];
</code></pre>

The example uses the [reflection factory from laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/reflection-abstract-factory/) to resolve the constructor dependencies for the listener class.

If the listener has no dependencies, use the [factory `Laminas\ServiceManager\Factory\InvokableFactory`](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/#factories).

## Logging Message

To log a message, produce a [dispatch error](https://docs.laminas.dev/laminas-mvc/mvc-event/#mvceventevent_dispatch_error-dispatcherror) by navigate to `http://localhost:8080/1234`, and the 404 page should be displayed.
The attached listener will log a message and write it to the registered storage backend(s).

## Learn More

- [Listener Aggregates](../aggregates.md)
- [The MvcEvent](https://docs.laminas.dev/laminas-mvc/mvc-event/)
