<?php

namespace LaminasBench\EventManager;

use Laminas\Stdlib\DispatchableInterface;

trait BenchTrait
{
    /** @var int */
    private $numListeners = 50;

    private function generateCallback(): callable
    {
        return function ($e) {
        };
    }

    /** @return non-empty-string[] */
    private function getEventList(): array
    {
        return [
            'dispatch',
            'dispatch.post',
            '*',
        ];
    }

    /** @return class-string[] */
    private function getIdentifierList(): array
    {
        return [
            DispatchableInterface::class,
            'Laminas\Mvc\Controller\AbstractController',
            'Laminas\Mvc\Controller\AbstractActionController',
            'Laminas\Mvc\Controller\AbstractRestfulController',
            'Laminas\ApiTools\Rest\RestController',
            'CustomRestController',
        ];
    }
}
