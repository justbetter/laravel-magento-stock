<?php

namespace JustBetter\MagentoStock\Retriever;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoStock\Contracts\RetrievesStockSkus;

class DummySkuRetriever implements RetrievesStockSkus
{
    public function retrieveAll(): Enumerable
    {
        return collect(['::sku_1::', '::sku_2::']);
    }

    public function retrieveUpdated(?Carbon $from = null): Enumerable
    {
        return collect(['::sku_1::']);
    }
}
