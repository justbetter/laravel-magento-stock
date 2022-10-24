<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Data\StockData;

interface DeterminesStockModified
{
    public function modified(StockData $stockData): bool;
}
