<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use JustBetter\MagentoStock\Actions\ProcessStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class ProcessStockTest extends TestCase
{
    public function test_it_creates_model(): void
    {
        /** @var ProcessStock $action */
        $action = app(ProcessStock::class);

        $data = StockData::make('::sku::');

        $action->process($data);

        $model = MagentoStock::query()
            ->where('sku', '=', '::sku::')
            ->first();

        $this->assertNotNull($model);
    }
}
