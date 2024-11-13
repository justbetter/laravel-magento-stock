<?php

namespace JustBetter\MagentoStock\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Exceptions\NotImplementedException;

class Repository extends BaseRepository
{
    public function retrieve(string $sku): ?StockData
    {
        throw new NotImplementedException;
    }

    public function skus(?Carbon $from = null): Enumerable
    {
        /** @var Enumerable<int, string> $skus */
        $skus = MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->select(['sku'])
            ->distinct()
            ->pluck('sku');

        return $skus;
    }
}
