<?php

namespace JustBetter\MagentoStock\Retriever;

use JustBetter\MagentoStock\Contracts\RetrievesStock;
use JustBetter\MagentoStock\Data\StockData;

class DummyStockRetriever implements RetrievesStock
{
    public function retrieve(string $sku): ?StockData
    {
        return StockData::make($sku, random_int(0, 10));
    }
}
