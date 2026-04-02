<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Fakes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Repositories\Repository;

class FakeRepository extends Repository
{
    protected string $name = 'Fake Repository';

    public function retrieve(string $sku): ?StockData
    {
        return StockData::of([
            'sku' => $sku,
            'quantity' => 10,
            'in_stock' => true,
        ]);
    }

    #[\Override]
    public function skus(?Carbon $from = null): Collection
    {
        return collect(['sku_1', 'sku_2']);
    }
}
