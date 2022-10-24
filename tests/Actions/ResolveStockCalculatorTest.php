<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use JustBetter\MagentoStock\Actions\ResolveStockCalculator;
use JustBetter\MagentoStock\Calculator\StockCalculator;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\TestCase;

class ResolveStockCalculatorTest extends TestCase
{
    public function test_it_resolves(): void
    {
        $action = new ResolveStockCalculator();

        $calculator = $action->resolve(new StockData('::sku::'));

        $this->assertTrue(is_a($calculator, StockCalculator::class));
    }
}
