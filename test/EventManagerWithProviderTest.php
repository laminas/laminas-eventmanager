<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\Exception\RuntimeException;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerAttachmentInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Demonstrate usage with an explicitly provided ListenerProvider
 */
class EventManagerWithProviderTest extends TestCase
{
    use DeprecatedAssertions;

    public function testCanCreateInstanceWithListenerProvider()
    {
        $provider = $this->prophesize(ListenerProviderInterface::class)->reveal();

        $manager = EventManager::createUsingListenerProvider($provider);

        $this->assertInstanceOf(EventManager::class, $manager);
        $this->assertAttributeSame($provider, 'provider', $manager);
        $this->assertAttributeEmpty('prioritizedProvider', $manager);

        return $manager;
    }

    public function testCanCreateInstanceWithPrioritizedListenerProvider()
    {
        $provider = $this->prophesize(ListenerProviderInterface::class);
        $provider->willImplement(PrioritizedListenerAttachmentInterface::class);

        $manager = EventManager::createUsingListenerProvider($provider->reveal());

        $this->assertInstanceOf(EventManager::class, $manager);
        $this->assertAttributeSame($provider->reveal(), 'provider', $manager);
        $this->assertAttributeSame($provider->reveal(), 'prioritizedProvider', $manager);
    }

    public function attachableProviderMethods(): array
    {
        $listener = function (object $e): void {
        };
        return [
            'attach'                 => ['attach', ['foo', $listener, 100]],
            'attachWildcardListener' => ['attachWildcardListener', [$listener, 100]],
            'detach'                 => ['detach', [$listener, 'foo']],
            'detachWildcardListener' => ['detachWildcardListener', [$listener]],
            'clearListeners'         => ['clearListeners', ['foo']],
        ];
    }

    /**
     * @dataProvider attachableProviderMethods
     * @depends testCanCreateInstanceWithListenerProvider
     * @param string $method Method to call on manager
     * @param array $arguments Arguments to pass to $method
     * @param EventManager $manager Event manager on which to call $method
     */
    public function testAttachmentMethodsRaiseExceptionForNonAttachableProvider(
        string $method,
        array $arguments,
        EventManager $manager
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('instance is not of type ' . PrioritizedListenerAttachmentInterface::class);
        $manager->{$method}(...$arguments);
    }

    /**
     * @dataProvider attachableProviderMethods
     * @depends testCanCreateInstanceWithPrioritizedListenerProvider
     * @param string $method Method to call on manager
     * @param array $arguments Arguments to pass to $method
     */
    public function testAttachmentMethodsProxyToAttachableProvider(string $method, array $arguments): void
    {
        // Creating instances here, because prophecies cannot be passed as dependencies
        $provider = $this->prophesize(ListenerProviderInterface::class);
        $provider->willImplement(PrioritizedListenerAttachmentInterface::class);
        $provider
            ->attachWildcardListener(
                Argument::type('callable'),
                Argument::type('int')
            )
            ->will(function ($args) {
                return array_shift($args);
            });

        $manager = EventManager::createUsingListenerProvider($provider->reveal());

        $manager->{$method}(...$arguments);

        $provider->{$method}(...$arguments)->shouldHaveBeenCalledTimes(1);
    }

    public function testGetListenersForEventProxiesToProvider()
    {
        $event    = (object) ['name' => 'test'];
        $listener = function (object $e): void {
        };

        $listeners = [
            clone $listener,
            clone $listener,
            clone $listener,
        ];

        $provider = $this->prophesize(ListenerProviderInterface::class);
        $provider
            ->getListenersForEvent($event, [])
            ->willReturn($listeners);

        $manager = EventManager::createUsingListenerProvider($provider->reveal());

        $test = $manager->getListenersForEvent($event);

        $this->assertSame($listeners, iterator_to_array($test, false));
    }
}
