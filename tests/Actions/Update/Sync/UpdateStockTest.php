<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Sync;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateStock;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesBackorders;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesMsiStock;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesSimpleStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeBackorderRepository;
use JustBetter\MagentoStock\Tests\Fakes\FakeMsiRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateStockTest extends TestCase
{
    #[Test]
    public function it_resets_update_when_product_does_not_exist(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->andReturnFalse();
        });

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var UpdateStock $action */
        $action = app(UpdateStock::class);
        $action->update($model);

        $model->refresh();

        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_updates_simple_stock_and_backorders(): void
    {
        config()->set('magento-stock.repository', FakeBackorderRepository::class);
        Event::fake();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        $this->mock(UpdatesBackorders::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesSimpleStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var UpdateStock $action */
        $action = app(UpdateStock::class);
        $action->update($model);

        $model->refresh();

        $this->assertFalse($model->update);
        $this->assertNotNull($model->last_updated);

        Event::assertDispatched(StockUpdatedEvent::class);
    }

    #[Test]
    public function it_updates_msi_stock(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);

        Event::fake();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        $this->mock(UpdatesMsiStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var UpdateStock $action */
        $action = app(UpdateStock::class);
        $action->update($model);

        $model->refresh();

        $this->assertFalse($model->update);
        $this->assertNotNull($model->last_updated);

        Event::assertDispatched(StockUpdatedEvent::class);
    }
}
