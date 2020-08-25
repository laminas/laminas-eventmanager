<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\Filter\FilterIterator;
use Laminas\EventManager\FilterChain;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Stdlib
 */
class FilterChainTest extends TestCase
{
    /**
     * @var FilterChain
     */
    protected $filterchain;

    protected function setUp() : void
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->filterchain = new FilterChain;
    }

    public function testSubscribeShouldReturnCallbackHandler()
    {
        $handle = $this->filterchain->attach([ $this, __METHOD__ ]);
        self::assertSame([ $this, __METHOD__ ], $handle);
    }

    public function testSubscribeShouldAddCallbackHandlerToFilters()
    {
        $handler  = $this->filterchain->attach([$this, __METHOD__]);
        $handlers = $this->filterchain->getFilters();
        self::assertEquals(1, count($handlers));
        self::assertTrue($handlers->contains($handler));
    }

    public function testDetachShouldRemoveCallbackHandlerFromFilters()
    {
        $handle = $this->filterchain->attach([ $this, __METHOD__ ]);
        $handles = $this->filterchain->getFilters();
        self::assertTrue($handles->contains($handle));
        $this->filterchain->detach($handle);
        $handles = $this->filterchain->getFilters();
        self::assertFalse($handles->contains($handle));
    }

    public function testDetachShouldReturnFalseIfCallbackHandlerDoesNotExist()
    {
        $handle1 = $this->filterchain->attach([ $this, __METHOD__ ]);
        $this->filterchain->clearFilters();
        $handle2 = $this->filterchain->attach([ $this, 'handleTestTopic' ]);
        self::assertFalse($this->filterchain->detach($handle1));
    }

    public function testRetrievingAttachedFiltersShouldReturnEmptyArrayWhenNoFiltersExist()
    {
        $handles = $this->filterchain->getFilters();
        self::assertEquals(0, count($handles));
    }

    public function testFilterChainShouldReturnLastResponse()
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            $return = $chain->next($context, $params, $chain);
            return $return;
        });
        $this->filterchain->attach(function ($context, array $params) {
            $string = isset($params['string']) ? $params['string'] : '';
            return str_rot13($string);
        });
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        self::assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function testFilterIsPassedContextAndArguments()
    {
        $this->filterchain->attach([ $this, 'filterTestCallback1' ]);
        $obj = (object) ['foo' => 'bar', 'bar' => 'baz'];
        $value = $this->filterchain->run($this, ['object' => $obj]);
        self::assertEquals('filtered', $value);
        self::assertEquals('filterTestCallback1', $this->message);
        self::assertEquals('foobarbaz', $obj->foo);
    }

    public function testInterceptingFilterShouldReceiveChain()
    {
        $this->filterchain->attach([$this, 'filterReceivalCallback']);
        $this->filterchain->run($this);
    }

    public function testFilteringStopsAsSoonAsAFilterFailsToCallNext()
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            return $chain->next($context, $params, $chain);
        }, 10000);
        $this->filterchain->attach(function ($context, array $params) {
            $string = isset($params['string']) ? $params['string'] : '';
            return str_rot13($string);
        }, 1000);
        $this->filterchain->attach(function ($context, $params, $chain) {
            $string = isset($params['string']) ? $params['string'] : '';
            return hash('md5', $string);
        }, 100);
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        self::assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }

    public function filterTestCallback1($context, array $params)
    {
        $context->message = __FUNCTION__;
        if (isset($params['object']) && is_object($params['object'])) {
            $params['object']->foo = 'foobarbaz';
        }
        return 'filtered';
    }

    public function filterReceivalCallback($context, array $params, $chain)
    {
        self::assertInstanceOf(FilterIterator::class, $chain);
    }

    public function testRunReturnsNullWhenChainIsEmpty()
    {
        $filterChain = new FilterChain();
        self::assertNull($filterChain->run(null));
    }

    public function testGetResponses()
    {
        $filterChain = new FilterChain();
        self::assertNull($filterChain->getResponses());
    }
}
