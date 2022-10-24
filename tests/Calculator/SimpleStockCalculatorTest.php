<?php

namespace JustBetter\MagentoStock\Tests\Calculator;

use JustBetter\MagentoStock\Calculator\SimpleStockCalculator;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\TestCase;

class SimpleStockCalculatorTest extends TestCase
{
    protected StockData $data;

    protected SimpleStockCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = new StockData('::sku::', 10);

        $this->calculator = new SimpleStockCalculator($this->data);
    }

    public function test_it_returns_backorders(): void
    {
        $this->assertEquals(false, $this->calculator->getBackorders());
    }

    public function test_it_returns_quantity(): void
    {
        $this->assertEquals(10, $this->calculator->calculateQuantity());
    }

    public function test_it_simple_in_stock(): void
    {
        config()->set('magento-stock.msi', false);

        $this->assertEquals(true, $this->calculator->isInStock());
    }

    public function test_it_simple_out_of_stock(): void
    {
        $this->data->quantity = 0;

        $calculator = new SimpleStockCalculator($this->data);

        $this->assertEquals(false, $calculator->isInStock());
    }

    public function test_it_returns_msi_statuses(): void
    {
        $this->data->msiStatus = [
            'A' => true,
        ];

        $calculator = new SimpleStockCalculator($this->data);

        $this->assertEquals($this->data->msiStatus, $calculator->calculateMsiStatusses());
    }

    public function test_it_returns_msi_quantities(): void
    {
        $this->data->msiQuantity = [
            'A' => 10,
        ];

        $calculator = new SimpleStockCalculator($this->data);

        $this->assertEquals($this->data->msiQuantity, $calculator->calculateMsiQuantities());
    }

    public function test_it_msi_in_stock(): void
    {
        config()->set('magento-stock.msi', true);

        $this->data->msiQuantity = [
            'A' => 10,
            'B' => 0,
        ];

        $calculator = new SimpleStockCalculator($this->data);

        $this->assertEquals(true, $calculator->isInStock());
    }

    public function test_it_msi_out_of_stock(): void
    {
        config()->set('magento-stock.msi', true);

        $this->data->msiQuantity = [
            'A' => 0,
            'B' => 0,
        ];

        $calculator = new SimpleStockCalculator($this->data);

        $this->assertEquals(false, $calculator->isInStock());
    }
}
