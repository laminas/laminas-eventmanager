<?php

namespace LaminasTest\EventManager\TestAsset;

class Functor
{
    public function __invoke($e)
    {
        return __METHOD__;
    }
}
