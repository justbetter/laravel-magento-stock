<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use JustBetter\MagentoStock\Actions\DetermineStockModified;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\TestCase;

class DetermineStockModifiedTest extends TestCase
{
    public function test_it_returns_true_for_new_sku(): void
    {
        $action = new DetermineStockModified();
        $data = StockData::make('::some_new_sku::');

        $this->assertTrue($action->modified($data));
    }

    public function test_it_checks_equality(): void
    {
        $action = new DetermineStockModified();
        $data = StockData::make('::some_sku::');

        $data->toModel()->save();

        $this->assertFalse($action->modified($data));

        $data->setQuantity(10);
        $this->assertTrue($action->modified($data));
    }
}
