<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Data\StockData;

interface RetrievesStock
{
    public function retrieve(string $sku): ?StockData;
}
