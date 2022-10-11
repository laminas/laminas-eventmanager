<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use Laminas\EventManager\Filter\FilterIterator;
use Laminas\EventManager\FilterChain;
use PHPUnit\Framework\TestCase;

use function hash;
use function str_rot13;
use function trim;

class FilterChainTest extends TestCase
{
    private FilterChain $filterchain;

    private string|null $message;

    protected function setUp(): void
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->filterchain = new FilterChain();
    }

    public function testSubscribeShouldReturnCallbackHandler(): void
    {
        $callback = static fn (): int => 0;
        $handle   = $this->filterchain->attach($callback);
        self::assertSame($callback, $handle);
    }

    public function testSubscribeShouldAddCallbackHandlerToFilters(): void
    {
        $handler  = $this->filterchain->attach(static fn (): int => 0);
        $handlers = $this->filterchain->getFilters();
        self::assertCount(1, $handlers);
        self::assertTrue($handlers->contains($handler));
    }

    public function testDetachShouldRemoveCallbackHandlerFromFilters(): void
    {
        $handle  = $this->filterchain->attach(static fn (): int => 0);
        $handles = $this->filterchain->getFilters();
        self::assertTrue($handles->contains($handle));
        $this->filterchain->detach($handle);
        $handles = $this->filterchain->getFilters();
        self::assertFalse($handles->contains($handle));
    }

    public function testDetachShouldReturnFalseIfCallbackHandlerDoesNotExist(): void
    {
        $handle1 = $this->filterchain->attach(static fn (): int => 1);
        $this->filterchain->clearFilters();
        $this->filterchain->attach(static fn (): int => 2);
        self::assertFalse($this->filterchain->detach($handle1));
    }

    public function testRetrievingAttachedFiltersShouldReturnEmptyArrayWhenNoFiltersExist(): void
    {
        $handles = $this->filterchain->getFilters();
        self::assertCount(0, $handles);
    }

    public function testFilterChainShouldReturnLastResponse(): void
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            return $chain->next($context, $params, $chain);
        });
        $this->filterchain->attach(function ($context, array $params) {
            $string = $params['string'] ?? '';
            return str_rot13($string);
        });
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        self::assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function testFilterIsPassedContextAndArguments(): void
    {
        $this->filterchain->attach(function (self $context, array $params): string {
            self::assertSame($this, $context);
            self::assertIsObject($params['object']);
            $params['object']->foo = 'foobarbaz';

            return 'Expected Output';
        });
        $obj   = (object) ['foo' => 'bar', 'bar' => 'baz'];
        $value = $this->filterchain->run($this, ['object' => $obj]);
        self::assertEquals('Expected Output', $value);
        self::assertEquals('foobarbaz', $obj->foo);
    }

    public function testInterceptingFilterShouldReceiveChain(): void
    {
        /** @psalm-suppress UnusedClosureParam */
        $this->filterchain->attach(function (self $context, array $params, mixed $chain) {
            self::assertInstanceOf(FilterIterator::class, $chain);
        });
        $this->filterchain->run($this);
    }

    /** @psalm-suppress UnusedClosureParam */
    public function testFilteringStopsAsSoonAsAFilterFailsToCallNext(): void
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            return $chain->next($context, $params, $chain);
        }, 10000);
        $this->filterchain->attach(function ($context, array $params) {
            $string = $params['string'] ?? '';
            return str_rot13($string);
        }, 1000);
        $this->filterchain->attach(function ($context, array $params) {
            $string = $params['string'] ?? '';
            return hash('md5', $string);
        }, 100);
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        self::assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function testRunReturnsNullWhenChainIsEmpty(): void
    {
        $filterChain = new FilterChain();
        self::assertNull($filterChain->run(null));
    }

    public function testGetResponses(): void
    {
        $filterChain = new FilterChain();
        self::assertNull($filterChain->getResponses());
    }
}
