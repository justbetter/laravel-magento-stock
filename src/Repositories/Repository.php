<?php

namespace JustBetter\MagentoStock\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Exceptions\NotImplementedException;

class Repository extends BaseRepository
{
    public function retrieve(string $sku): ?StockData
    {
        throw new NotImplementedException;
    }

    public function skus(?Carbon $from = null): Collection
    {
        /** @var Collection<int, string> $skus */
        $skus = MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->pluck('sku');

        return $skus;
    }
}
