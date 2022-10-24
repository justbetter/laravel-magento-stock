<?php

namespace JustBetter\MagentoStock\Actions;

use JustBetter\MagentoStock\Calculator\StockCalculator;
use JustBetter\MagentoStock\Contracts\ResolvesStockCalculator;
use JustBetter\MagentoStock\Data\StockData;

class ResolveStockCalculator implements ResolvesStockCalculator
{
    public function resolve(StockData $stockData): StockCalculator
    {
        /** @var class-string<StockCalculator> $calculatorClass */
        $calculatorClass = config('magento-stock.calculator');

        return new $calculatorClass($stockData);
    }

    public static function bind(): void
    {
        app()->singleton(ResolvesStockCalculator::class, static::class);
    }
}
