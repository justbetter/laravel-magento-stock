<?php

namespace JustBetter\MagentoStock\Tests\Fakes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Repositories\Repository;

class FakeNullRepository extends Repository
{
    protected string $name = 'Fake Null Repository';

    public function retrieve(string $sku): ?StockData
    {
        return null;
    }

    public function skus(?Carbon $from = null): Collection
    {
        return collect(['sku_1', 'sku_2']);
    }
}
