<?php

namespace LaminasPsalm\EventManager;

use Laminas\EventManager\EventInterface;

class EventInterfaceChecks
{
    /**
     * @param EventInterface<CheckObject, array{foo: int, bar: CheckObject}> $e
     * @return array{CheckObject, array{foo: int, bar: CheckObject}}
     */
    function checkTargetAndParamsMatchTemplate(EventInterface $e): array
    {
        return [
            $e->getTarget(),
            $e->getParams(),
        ];
    }

    /**
     * @param EventInterface<null, array{foo: int, bar: CheckObject}> $e
     * @return array{int, CheckObject}
     */
    function checkIndividualParamsMatchTemplate(EventInterface $e): array
    {
        return [
            $e->getParams()['foo'],
            $e->getParams()['bar'],
        ];
    }

    // TODO: Check ctor inferrence, setParams setTarget out changes, ignore setParam() or getParam()
}