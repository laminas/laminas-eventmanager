<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager\ListenerProvider;

use Laminas\EventManager\Event;
use Laminas\EventManager\Exception;
use Laminas\EventManager\ListenerProvider\PrioritizedAggregateListenerProvider;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerProvider;
use Laminas\EventManager\ListenerProvider\PrioritizedListenerProviderInterface;
use Laminas\EventManager\ListenerProvider\PrioritizedIdentifierListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;

class PrioritizedAggregateListenerProviderTest extends TestCase
{
    public function invalidProviders(): array
    {
        $genericProvider = $this->prophesize(ListenerProviderInterface::class)->reveal();
        return [
            'null'                     => [null],
            'true'                     => [true],
            'false'                    => [false],
            'zero'                     => [0],
            'int'                      => [1],
            'zero-float'               => [0.0],
            'float'                    => [1.1],
            'string'                   => ['invalid'],
            'array'                    => [['invalid']],
            'object'                   => [(object) ['value' => 'invalid']],
            'non-prioritized-provider' => [$genericProvider],
        ];
    }

    /**
     * @dataProvider invalidProviders
     * @param mixed $provider
     */
    public function testConstructorRaisesExceptionForInvalidProviders($provider)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(PrioritizedListenerProviderInterface::class);
        new PrioritizedAggregateListenerProvider([$provider]);
    }

    public function testIteratesProvidersInOrderExpected()
    {
        $event = new Event();
        $event->setName('test');

        $baseListener = function () {
        };

        $first   = clone $baseListener;
        $second  = clone $baseListener;
        $third   = clone $baseListener;
        $fourth  = clone $baseListener;
        $fifth   = clone $baseListener;
        $sixth   = clone $baseListener;
        $seventh = clone $baseListener;
        $eighth  = clone $baseListener;
        $ninth   = clone $baseListener;

        $provider = new PrioritizedListenerProvider();
        $provider->attachWildcardListener($first);
        $provider->attach(Event::class, $second);
        $provider->attach('test', $third);

        $identifiedProvider = new PrioritizedIdentifierListenerProvider();
        $identifiedProvider->attach(Event::class, '*', $fourth);
        $identifiedProvider->attach(Event::class, Event::class, $fifth);
        $identifiedProvider->attach(Event::class, 'test', $sixth);
        $identifiedProvider->attach('*', '*', $seventh);
        $identifiedProvider->attach('*', Event::class, $eighth);
        $identifiedProvider->attach('*', 'test', $ninth);

        $aggregateProvider = new PrioritizedAggregateListenerProvider([
            $provider,
            $identifiedProvider,
        ]);

        $prioritizedListeners = [];
        $index = 1;

        foreach ($aggregateProvider->getListenersForEvent($event, [Event::class]) as $listener) {
            $prioritizedListeners[$index] = spl_object_hash($listener);
            $index += 1;
        }

        $expected = [
            1 => spl_object_hash($third),
            2 => spl_object_hash($second),
            3 => spl_object_hash($first),
            4 => spl_object_hash($sixth),
            5 => spl_object_hash($fifth),
            6 => spl_object_hash($fourth),
            7 => spl_object_hash($ninth),
            8 => spl_object_hash($eighth),
            9 => spl_object_hash($seventh),
        ];

        $this->assertSame($expected, $prioritizedListeners);
    }
}
