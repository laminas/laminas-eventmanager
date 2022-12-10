<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager\Model;

use Laminas\EventManager\Event;

/**
 * @extends Event<CheckObject|null, array{foo: string}>
 */
class CheckEvent extends Event
{
}
