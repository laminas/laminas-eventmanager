<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventInterface;

class Functor
{
    /** @param string|EventInterface $e */
    public function __invoke($e): string
    {
        return __METHOD__;
    }
}
