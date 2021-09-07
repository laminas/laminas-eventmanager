<?php

namespace LaminasTest\EventManager;

use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\Filter\FilterIterator;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Stdlib
 */
class FilterIteratorTest extends TestCase
{
    public function testNextReturnsNullOnEmptyChain()
    {
        $filterIterator = new FilterIterator();
        self::assertNull($filterIterator->next([]));
    }

    public function testNextReturnsNullWithEmptyHeap()
    {
        $filterIterator = new FilterIterator();
        self::assertNull($filterIterator->next([0, 1, 2]));
    }

    public function testContainsReturnsFalseForInvalidElement()
    {
        $filterIterator = new FilterIterator();
        self::assertFalse($filterIterator->contains('foo'));
    }

    public function testContainsReturnsTrueForValidElement()
    {
        $callback       = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);
        self::assertTrue($filterIterator->contains($callback));
    }

    public function testRemoveFromEmptyQueueReturnsFalse()
    {
        $filterIterator = new FilterIterator();

        self::assertFalse($filterIterator->remove('foo'));
    }

    public function testRemoveUnrecognizedItemFromQueueReturnsFalse()
    {
        $callback       = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);

        self::assertFalse($filterIterator->remove(clone $callback));
    }

    public function testRemoveValidItemFromQueueReturnsTrue()
    {
        $callback       = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);

        self::assertTrue($filterIterator->remove($callback));
    }

    public function testNextReturnsNullWhenFilterChainIsEmpty()
    {
        $filterIterator = new FilterIterator();

        $chain = new FilterIterator();

        self::assertNull($filterIterator->next([0, 1, 2], ['foo', 'bar'], $chain));
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public function invalidFilters(): array
    {
        return [
            'null'                 => [null],
            'true'                 => [true],
            'false'                => [false],
            'zero'                 => [0],
            'int'                  => [1],
            'zero-float'           => [0.0],
            'float'                => [1.1],
            'non-callable-string'  => ['not a function'],
            'non-callable-array'   => [['not a function']],
            'non-invokable-object' => [(object) ['__invoke' => 'not a function']],
        ];
    }

    /**
     * @dataProvider invalidFilters
     * @param mixed $filter
     */
    public function testInsertShouldRaiseExceptionOnNonCallableDatum($filter)
    {
        $iterator = new FilterIterator();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('callables');
        $iterator->insert($filter, 1);
    }
}
