<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use LaminasTest\EventManager\TestAsset\MockListenerAggregateTrait;
use PHPUnit\Framework\TestCase;

use function in_array;

class ListenerAggregateTraitTest extends TestCase
{
    /** @var class-string<ListenerAggregateInterface> */
    public $aggregateClass = MockListenerAggregateTrait::class;

    public function testDetachRemovesAttachedListeners(): void
    {
        $class     = $this->aggregateClass;
        $aggregate = new $class();

        $events = $this->createMock(EventManagerInterface::class);
        $events->expects(self::atLeast(2))
            ->method('attach')
            ->with(
                self::callback(static function (string $value): bool {
                    self::assertTrue(in_array($value, ['foo.bar', 'foo.baz'], true));

                    return true;
                }),
                self::callback(static function (array $value) use ($aggregate): bool {
                    self::assertSame($aggregate, $value[0] ?? null);
                    self::assertSame('doFoo', $value[1] ?? null);

                    return true;
                }),
            )->willReturnArgument(1);

        $events->expects(self::exactly(2))
            ->method('detach')
            ->with([$aggregate, 'doFoo']);

        $aggregate->attach($events);

        $listeners = $aggregate->getCallbacks();
        self::assertIsArray($listeners);
        self::assertCount(2, $listeners);

        foreach ($listeners as $listener) {
            self::assertSame([$aggregate, 'doFoo'], $listener);
        }

        $aggregate->detach($events);

        self::assertSame([], $aggregate->getCallbacks());
    }
}
