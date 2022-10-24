<?php

namespace JustBetter\MagentoStock\Tests\Data;

use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\TestCase;

class StockDataTest extends TestCase
{
    public function test_msi_setters(): void
    {
        $data = StockData::make('::sku::');

        $data->setMsiQuantity('A', 10);
        $data->setMsiStatus('A', false);

        $this->assertEquals(['A' => 10], $data->msiQuantity);
        $this->assertEquals(['A' => false], $data->msiStatus);
    }
}
