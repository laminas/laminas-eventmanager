<?php

namespace LaminasTest\EventManager\TestAsset;

class CountingListener
{
    public $count = 0;

    public function __invoke($e)
    {
        $this->count += 1;
    }
}
