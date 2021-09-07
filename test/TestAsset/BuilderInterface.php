<?php

namespace LaminasTest\EventManager\TestAsset;

use Interop\Container\ContainerInterface;

/**
 * Mimic the ServiceManager v3 ServiceLocatorInterface in order to test
 * lazy listener creation.
 */
interface BuilderInterface extends ContainerInterface
{
    /** @param object $service */
    public function build($service, array $opts = []);
}
