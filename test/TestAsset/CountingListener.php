<?php

namespace LaminasTest\EventManager\TestAsset;

use Laminas\EventManager\EventInterface;

class CountingListener
{
    /** @var int */
    public $count = 0;

    /** @param string|EventInterface $e */
    public function __invoke($e): void
    {
        $this->count += 1;
    }
}
