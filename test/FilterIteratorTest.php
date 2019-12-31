<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\Exception\InvalidArgumentException;
use Laminas\EventManager\Filter\FilterIterator;

/**
 * @group      Laminas_Stdlib
 */
class FilterIteratorTest extends \PHPUnit_Framework_TestCase
{

    public function testNextReturnsNullOnEmptyChain()
    {
        $filterIterator = new FilterIterator();
        $this->assertNull($filterIterator->next([]));
    }

    public function testNextReturnsNullWithEmptyHeap()
    {
        $filterIterator = new FilterIterator();
        $this->assertNull($filterIterator->next([0, 1, 2]));
    }

    public function testContainsReturnsFalseForInvalidElement()
    {
        $filterIterator = new FilterIterator();
        $this->assertFalse($filterIterator->contains('foo'));
    }

    public function testContainsReturnsTrueForValidElement()
    {
        $callback = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);
        $this->assertTrue($filterIterator->contains($callback));
    }

    public function testRemoveFromEmptyQueueReturnsFalse()
    {
        $filterIterator = new FilterIterator();

        $this->assertFalse($filterIterator->remove('foo'));
    }

    public function testRemoveUnrecognizedItemFromQueueReturnsFalse()
    {
        $callback = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);

        $this->assertFalse($filterIterator->remove(clone $callback));
    }

    public function testRemoveValidItemFromQueueReturnsTrue()
    {
        $callback = function () {
        };
        $filterIterator = new FilterIterator();
        $filterIterator->insert($callback, 1);

        $this->assertTrue($filterIterator->remove($callback));
    }

    public function testNextReturnsNullWhenFilterChainIsEmpty()
    {
        $filterIterator = new FilterIterator();

        $chain = new FilterIterator();

        $this->assertNull($filterIterator->next([0, 1, 2], ['foo', 'bar'], $chain));
    }

    public function invalidFilters()
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
     */
    public function testInsertShouldRaiseExceptionOnNonCallableDatum($filter)
    {
        $iterator = new FilterIterator();
        $this->setExpectedException(InvalidArgumentException::class, 'callables');
        $iterator->insert($filter, 1);
    }
}
