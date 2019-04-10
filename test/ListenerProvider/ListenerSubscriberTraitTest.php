<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

use Closure;
use Laminas\EventManager\ListenerProvider\ListenerSubscriberInterface;
use Laminas\EventManager\ListenerProvider\ListenerSubscriberTrait;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerAttachmentInterface;
use PHPUnit\Framework\TestCase;

class ListenerSubscriberTraitTest extends TestCase
{
    /**
     * @return ListenerSubscriberInterface
     */
    public function createProvider(callable $attachmentCallback)
    {
        return new TestAsset\CallbackSubscriber($attachmentCallback);
    }

    public function testSubscriberAttachesListeners()
    {
        $baseListener = function () {
        };
        $listener1    = clone $baseListener;
        $listener2    = clone $baseListener;
        $listener3    = clone $baseListener;

        $provider = $this->prophesize(PrioritizedListenerAttachmentInterface::class);
        $provider->attach('foo.bar', $listener1, 100)->will(function ($args) {
            return $args[1];
        });
        $provider->attach('foo.baz', $listener2, 100)->will(function ($args) {
            return $args[1];
        });

        $subscriber = $this->createProvider(function ($provider, $priority) use ($listener1, $listener2) {
            $this->listeners[] = $provider->attach('foo.bar', $listener1, $priority);
            $this->listeners[] = $provider->attach('foo.baz', $listener2, $priority);
        });

        $subscriber->attach($provider->reveal(), 100);

        $this->assertAttributeSame([$listener1, $listener2], 'listeners', $subscriber);

        return [
            'subscriber' => $subscriber,
            'provider'   => $provider,
            'listener1'  => $listener1,
            'listener2'  => $listener2,
        ];
    }

    /**
     * @depends testSubscriberAttachesListeners
     * @param  array $dependencies
     */
    public function testDetachRemovesAttachedListeners(array $dependencies)
    {
        $subscriber = $dependencies['subscriber'];
        $provider   = $dependencies['provider'];

        $provider->detach($dependencies['listener1'])->shouldBeCalledTimes(1);
        $provider->detach($dependencies['listener2'])->shouldBeCalledTimes(1);

        $subscriber->detach($provider->reveal());
        $this->assertAttributeSame([], 'listeners', $subscriber);
    }
}
