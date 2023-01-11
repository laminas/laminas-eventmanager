<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager;

use ArrayObject;
use Laminas\EventManager\EventManager;
use LaminasPsalm\EventManager\Model\CheckObject;

final class EventManagerCheck
{
    /** @return ArrayObject<string, CheckObject> */
    public function prepareArgsReturnsObjectWithCorrectType(): ArrayObject
    {
        $params = [
            'foo' => new CheckObject(),
            'bar' => new CheckObject(),
        ];

        return (new EventManager())->prepareArgs($params);
    }
}
