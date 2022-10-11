<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventInterface;

class CountingListener
{
    public int $count = 0;

    public int $index = 0;

    /** @param string|EventInterface $e */
    public function __invoke($e): void
    {
        $this->count += 1;
    }
}
