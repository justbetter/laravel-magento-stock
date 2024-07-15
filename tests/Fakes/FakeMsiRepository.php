<?php

namespace JustBetter\MagentoStock\Tests\Fakes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Repositories\Repository;

class FakeMsiRepository extends Repository
{
    protected string $name = 'Fake MSI Repository';

    public function retrieve(string $sku): ?StockData
    {
        return StockData::of([
            'sku' => $sku,
            'msi_quantity' => [
                'A' => 0,
                'B' => 10,
            ],
            'msi_status' => [
                'A' => false,
                'B' => true,
            ],
        ]);
    }

    public function skus(?Carbon $from = null): Collection
    {
        return collect(['sku_1', 'sku_2']);
    }
}
