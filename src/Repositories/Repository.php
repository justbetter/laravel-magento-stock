<?php

namespace JustBetter\MagentoStock\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoCustomers\Exceptions\NotImplementedException;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Data\StockData;

class Repository extends BaseRepository
{
    public function retrieve(string $sku): ?StockData
    {
        throw new NotImplementedException();
    }

    public function skus(?Carbon $from = null): Collection
    {
        return MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->pluck('sku');
    }
}
