<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use LaminasPsalm\EventManager\Model\CheckEvent;
use LaminasPsalm\EventManager\Model\CheckObject;

class EventChecks
{
    /**
     * @return array{
     *     Event,
     *     EventInterface,
     *     Event<null, array<empty, empty>>,
     *     EventInterface<null, array<empty, empty>>,
     * }
     */
    public function checkEmptyCtorInference(): array
    {
        $event = new Event();
        return [
            $event,
            $event,
            $event,
            $event,
        ];
    }

    /**
     * @return array{
     *     Event<'target-string', array{foo: 'bar', baz: true}>,
     *     'target-string',
     *     array{foo: 'bar', baz: true},
     * }
     */
    public function checkCtorInference(): array
    {
        $event = new Event(null, 'target-string', [
            'foo' => 'bar',
            'baz' => true,
        ]);
        return [
            $event,
            $event->getTarget(),
            $event->getParams(),
        ];
    }

    /**
     * Verifies that the psalm-this-out annotations are applied correctly to {@see Event}.
     *
     * @return Event<CheckObject, array{foo: 'bar'}>
     */
    public function checkThisOut(): Event
    {
        $event = new Event();
        $event->setTarget(new CheckObject());
        $event->setParams(['foo' => 'bar']);
        return $event;
    }

    /**
     * Verifies that the inherited psalm-this-out annotations do not change the class back to one of the inherited
     * classes. Note: This assumes child classes have no template variables.
     *
     * @return array {
     *      CheckEvent,
     *      Event<CheckObject, array{foo: 'bar'}>,
     *      EventInterface<CheckObject, array{foo: 'bar'}>,
     *      Event<'incorrect', array{foo: 'incorrect'}>,
     * }
     */
    public function checkThisOutInheritance(): array
    {
        $event = new CheckEvent('event-name', new CheckObject());
        $event->setParams(['foo' => 'bar']);
        return [
            $event,
            $event,
            $event,
            $event,
        ];
    }
}
