<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider\TestAsset;

class MultipleListener
{
    public function __invoke(object $e): void
    {
        $e->value = __FUNCTION__;
    }

    public function run(object $e): void
    {
        $e->value = __FUNCTION__;
    }

    public function onEvent(object $e): void
    {
        $e->value = __FUNCTION__;
    }
}
