<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager;

use Laminas\EventManager\EventInterface;

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

//    /**
//     * @param EventInterface $e
//     * @return array
//     */
//    public function checkSetParamDoesNotAlterTemplate(EventInterface $e): array
//    {
//
//    }

    // TODO: Check ctor inferrence, setParams setTarget out changes, ignore setParam() or getParam()
}
