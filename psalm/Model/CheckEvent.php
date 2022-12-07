<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager\Model;

use Laminas\EventManager\Event;

/**
 * @template TTarget of CheckObject|null
 * @extends Event<TTarget, array{foo: string}>
 */
class CheckEvent extends Event
{
}
