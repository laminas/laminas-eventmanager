<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager;

use Laminas\EventManager\EventInterface;
use LaminasPsalm\EventManager\Model\CheckObject;

class EventInterfaceChecks
{
    /**
     * @param EventInterface<CheckObject, array{foo: int, bar: CheckObject}> $e
     * @return array{
     *     CheckObject,
     *     array{foo: int, bar: CheckObject},
     *     int,
     *     CheckObject
     * }
     */
    public function checkTargetAndParamsMatchTemplate(EventInterface $e): array
    {
        return [
            $e->getTarget(),
            $e->getParams(),
            $e->getParams()['foo'],
            $e->getParams()['bar'],
        ];
    }

    /**
     * @param EventInterface<null, array<empty, empty>> $e
     * @return EventInterface<CheckObject, array<empty, empty>>
     */
    public function checkSetTargetChangesTemplate(EventInterface $e): EventInterface
    {
        $e->setTarget(new CheckObject());
        return $e;
    }

    /**
     * @param EventInterface<null, array{foo: int}> $e
     * @return EventInterface<null, array{foo: CheckObject, bar: 'baz'}>
     */
    public function checkSetParamsChangesTemplate(EventInterface $e): EventInterface
    {
        $e->setParams([
            'foo' => new CheckObject(),
            'bar' => 'baz',
        ]);
        return $e;
    }

    /**
     * Individual params obtained via `getParam()` can't be inferred because their keys/values can't be selected from
     * the template type.
     *
     * @param EventInterface<null, array{foo: int, bar: CheckObject}> $e
     * @return array{
     *     mixed,
     *     mixed
     * }
     */
    public function checkIndividualParamsNotInferred(EventInterface $e): array
    {
        return [
            $e->getParam('foo'),
            $e->getParam('bar'),
        ];
    }

    /**
     * Changing the template and statically checking individual values is not possible with Psalm because
     * key-of and value-of do not work on objects.
     *
     * @param EventInterface<null, array{foo: int}> $e
     * @return EventInterface<null, array{foo: int}>
     */
    public function checkIndividualParamDoesNotChangeTemplate(EventInterface $e): EventInterface
    {
        $e->setParam('foo', 'notAnInt');
        $e->setParam('bar', 'keyDidNotExist');
        return $e;
    }
}
