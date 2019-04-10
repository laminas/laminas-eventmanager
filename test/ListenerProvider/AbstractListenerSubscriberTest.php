<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

class AbstractListenerSubscriberTest extends ListenerSubscriberTraitTest
{
    /**
     * {@inheritDoc}
     */
    public function createProvider(callable $attachmentCallback)
    {
        return new TestAsset\ExtendedCallbackSubscriber($attachmentCallback);
    }
}
