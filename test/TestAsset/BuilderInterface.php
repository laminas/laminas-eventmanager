<?php

declare(strict_types=1);

namespace LaminasTest\EventManager\TestAsset;

use Psr\Container\ContainerInterface;

/**
 * Mimic the ServiceManager v3 ServiceLocatorInterface in order to test
 * lazy listener creation.
 */
interface BuilderInterface extends ContainerInterface
{
    /**
     * @param object $service
     * @return object
     */
    public function build($service, array $opts = []);
}
