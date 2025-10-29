<?php

declare(strict_types=1);

namespace LaminasPsalm\EventManager\Model;

use Laminas\EventManager\Event;

/**
 * Psalm type checking helper
 *
 * @internal
 *
 * @extends Event<CheckObject|null, array{foo: string}>
 * @final This class should not be extended
 */
class CheckEvent extends Event
{
}
