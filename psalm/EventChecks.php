<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;

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
}
