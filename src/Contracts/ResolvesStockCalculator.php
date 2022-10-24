<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Calculator\StockCalculator;
use JustBetter\MagentoStock\Data\StockData;

interface ResolvesStockCalculator
{
    public function resolve(StockData $stockData): StockCalculator;
}
