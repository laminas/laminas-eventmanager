<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use LaminasTest\EventManager\TestAsset\CountingListener;
use PHPUnit\Framework\TestCase;

use function array_rand;
use function range;
use function sprintf;

class SharedListenerIntegrationTest extends TestCase
{
    /** @var string[] */
    private array $identifiers;
    private SharedEventManager $sharedEvents;
    private EventManager $events;

    protected function setUp(): void
    {
        $this->identifiers  = ['Foo', 'Bar', 'Baz'];
        $this->sharedEvents = new SharedEventManager();
        $this->events       = new EventManager($this->sharedEvents, $this->identifiers);
    }

    public function testCanTriggerTheSameSharedListenerMultipleTimes(): void
    {
        $listener = new CountingListener();
        $this->sharedEvents->attach('Foo', 'foo', $listener);

        $iterations = array_rand(range(5, 100));
        for ($i = 0; $i < $iterations; $i += 1) {
            $this->events->trigger('foo');
        }
        self::assertSame($iterations, $listener->count);
    }

    public function testTriggeringSameEventMultipleTimesTriggersNewSharedListeners(): void
    {
        $listeners = [];

        for ($i = 0; $i < 5; $i += 1) {
            $listeners[$i] = $listener = new CountingListener();
            $this->sharedEvents->attach('Foo', 'foo', $listener);
            $this->events->trigger('foo');
        }

        for ($i = 0; $i < 5; $i += 1) {
            $expected = 5 - $i;
            $listener = $listeners[$i];
            self::assertSame(
                $expected,
                $listener->count,
                sprintf('Listener %s was not triggered expected %d times; instead %d', $i, $expected, $listener->count)
            );
        }
    }

    public function testTriggeringSameEventMultipleTimesDoesNotTriggersDetachedSharedListeners(): void
    {
        /** @var array<int, CountingListener> $listeners */
        $listeners    = [];
        $identifiers  = ['Foo', 'Bar', 'Baz'];
        $sharedEvents = new SharedEventManager();
        $events       = new EventManager($sharedEvents, $identifiers);

        for ($i = 0; $i < 5; $i += 1) {
            $listeners[$i]   = $listener = new CountingListener();
            $listener->index = $i;
            $sharedEvents->attach('Foo', 'foo', $listener);
        }

        for ($i = 0; $i < 5; $i += 1) {
            $sharedEvents->detach($listeners[$i], 'Foo', 'foo');
            $events->trigger('foo');
        }

        for ($i = 0; $i < 5; $i += 1) {
            $listener = $listeners[$i];
            self::assertEquals($i, $listener->count);
        }
    }
}
