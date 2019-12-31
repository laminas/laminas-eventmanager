<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

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

    public function testNextReturnsNullOnInvalidCallback()
    {
        $filterIterator = new FilterIterator();
        $filterIterator->insert(null, 1);
        $this->assertNull($filterIterator->next([0, 1, 2]));
    }

    public function testContainsReturnsFalseForInvalidElement()
    {
        $filterIterator = new FilterIterator();
        $this->assertFalse($filterIterator->contains('foo'));
    }

    public function testContainsReturnsTrueForValidElement()
    {
        $filterIterator = new FilterIterator();
        $filterIterator->insert('foo', 1);
        $this->assertTrue($filterIterator->contains('foo'));
    }

    public function testRemoveFromEmptyQueueReturnsFalse()
    {
        $filterIterator = new FilterIterator();

        $this->assertFalse($filterIterator->remove('foo'));
    }

    public function testRemoveInvalidItemFromQueueReturnsFalse()
    {
        $filterIterator = new FilterIterator();
        $filterIterator->insert('foo', 1);
        $filterIterator->insert('bar', 2);

        $this->assertTrue($filterIterator->remove('foo'));
    }

    public function testNextReturnsNullWhenFilterChainIsEmpty()
    {
        $filterIterator = new FilterIterator();

        $chain = new FilterIterator();

        $this->assertNull($filterIterator->next([0, 1, 2], ['foo', 'bar'], $chain));
    }
}
