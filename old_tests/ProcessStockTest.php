<?php

use JustBetter\MagentoStock\Actions\Retrieval\SaveStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class ProcessStockTest extends TestCase
{
    public function test_it_creates_model(): void
    {
        /** @var \JustBetter\MagentoStock\Tests\Actions\ProcessStock $action */
        $action = app(SaveStock::class);

        $data = StockData::make('::sku::');

        $action->process($data);

        $model = Stock::query()
            ->where('sku', '=', '::sku::')
            ->first();

        $this->assertNotNull($model);
    }
}
