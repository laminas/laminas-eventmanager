<?php

namespace LaminasBench\EventManager;

trait BenchTrait
{
    private $numListeners = 50;

    private function generateCallback()
    {
        return function ($e) {
        };
    }

    private function getEventList()
    {
        return [
            'dispatch',
            'dispatch.post',
            '*',
        ];
    }

    private function getIdentifierList()
    {
        return [
            'Laminas\Stdlib\DispatchableInterface',
            'Laminas\Mvc\Controller\AbstractController',
            'Laminas\Mvc\Controller\AbstractActionController',
            'Laminas\Mvc\Controller\AbstractRestfulController',
            'Laminas\ApiTools\Rest\RestController',
            'CustomRestController',
        ];
    }
}
