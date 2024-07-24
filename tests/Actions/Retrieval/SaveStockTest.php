<?php

namespace JustBetter\MagentoStock\Tests\Actions\Retrieval;

use JustBetter\MagentoStock\Actions\Retrieval\SaveStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SaveStockTest extends TestCase
{
    #[Test]
    public function it_saves_fields(): void
    {
        $stockData = StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
            'backorders' => Backorders::Backorders,
            'msi_quantity' => ['A' => 10],
            'msi_status' => ['A' => true],
        ]);

        /** @var SaveStock $action */
        $action = app(SaveStock::class);
        $action->save($stockData, false);

        /** @var Stock $model */
        $model = Stock::query()->firstWhere('sku', '=', '::sku::');

        $this->assertNotNull($model);
        $this->assertEquals('::sku::', $model->sku);
        $this->assertTrue($model->in_stock);
        $this->assertEquals(10, $model->quantity);
        $this->assertEquals(Backorders::Backorders, $model->backorders);
        $this->assertEquals(['A' => 10], $model->msi_stock);
        $this->assertEquals(['A' => true], $model->msi_status);
        $this->assertTrue($model->sync);
        $this->assertFalse($model->retrieve);
        $this->assertTrue($model->update);
        $this->assertNotNull($model->last_retrieved);
    }

    #[Test]
    public function it_does_not_set_update_when_unchanged(): void
    {
        $stockData = StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
        ]);

        /** @var SaveStock $action */
        $action = app(SaveStock::class);
        $action->save($stockData, false);

        /** @var Stock $model */
        $model = Stock::query()->firstWhere('sku', '=', '::sku::');

        $this->assertTrue($model->update);

        $model->update(['update' => false]);

        $action->save($stockData, false);

        $this->assertFalse($model->refresh()->update);
    }

    #[Test]
    public function it_can_force_update(): void
    {
        $stockData = StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
        ]);

        /** @var SaveStock $action */
        $action = app(SaveStock::class);
        $action->save($stockData, false);

        /** @var Stock $model */
        $model = Stock::query()->firstWhere('sku', '=', '::sku::');

        $this->assertTrue($model->update);

        $model->update(['update' => false]);

        $action->save($stockData, true);

        $this->assertTrue($model->refresh()->update);
    }
}
