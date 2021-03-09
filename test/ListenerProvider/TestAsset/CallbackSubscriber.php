<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider\TestAsset;

use Closure;
use Laminas\EventManager\ListenerProvider\ListenerSubscriberInterface;
use Laminas\EventManager\ListenerProvider\ListenerSubscriberTrait;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerAttachmentInterface;

class CallbackSubscriber implements ListenerSubscriberInterface
{
    use ListenerSubscriberTrait;

    /** @var Closure */
    private $attachmentCallback;

    public function __construct(callable $attachmentCallback)
    {
        $this->attachmentCallback = $attachmentCallback;
    }

    public function attach(PrioritizedListenerAttachmentInterface $provider, int $priority = 1): void
    {
        $attachmentCallback = $this->attachmentCallback->bindTo($this, $this);
        $attachmentCallback($provider, $priority);
    }
}
