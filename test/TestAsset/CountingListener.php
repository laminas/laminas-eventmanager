<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

/**
 * @final This class should not be extended
 */
class CountingListener
{
    public int $count = 0;

    public int $index = 0;

    public function __invoke(): void
    {
        $this->count += 1;
    }
}
