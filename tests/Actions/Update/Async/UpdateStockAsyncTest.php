<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Actions\Update\Async;

use JustBetter\MagentoStock\Actions\Update\Async\UpdateStockAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesBackordersAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesMsiStockAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesSimpleStockAsync;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeBackorderRepository;
use JustBetter\MagentoStock\Tests\Fakes\FakeMsiRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class UpdateStockAsyncTest extends TestCase
{
    #[Test]
    public function it_does_nothing_without_stocks(): void
    {
        config()->set('magento-stock.repository', FakeBackorderRepository::class);

        $this->mock(UpdatesBackordersAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $this->mock(UpdatesSimpleStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $this->mock(UpdatesMsiStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $stocks = collect();

        $action = app(UpdateStockAsync::class);

        $action->update($stocks);
    }

    #[Test]
    public function it_updates_backorder_and_simple_stock(): void
    {
        config()->set('magento-stock.repository', FakeBackorderRepository::class);

        $this->mock(UpdatesBackordersAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesSimpleStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesMsiStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $stocks = collect();

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_1::',
            'update' => true,
        ]);

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_2::',
            'update' => true,
        ]);

        $action = app(UpdateStockAsync::class);

        $action->update($stocks);

        $this->assertEquals(0, Stock::query()->where('update', '=', true)->count());
    }

    #[Test]
    public function it_does_not_clear_the_update_flag_when_the_stock_changed_concurrently(): void
    {
        config()->set('magento-stock.repository', FakeBackorderRepository::class);

        $this->mock(UpdatesBackordersAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesSimpleStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesMsiStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
            'checksum' => '::original::',
        ]);

        Stock::query()->whereKey($stock->id)->update(['checksum' => '::changed::']);

        $action = app(UpdateStockAsync::class);

        $action->update(collect([$stock]));

        /** @var Stock $fresh */
        $fresh = Stock::query()->findOrFail($stock->id);

        $this->assertTrue($fresh->update);
        $this->assertSame('::changed::', $fresh->checksum);
    }

    #[Test]
    public function it_updates_msi_stock(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);

        $this->mock(UpdatesBackordersAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $this->mock(UpdatesSimpleStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        $this->mock(UpdatesMsiStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $stocks = collect();

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_1::',
            'msi_status' => [
                'A' => true,
                'B' => true,
                'C' => true,
            ],
            'msi_stock' => [
                'A' => 10,
                'B' => 10,
                'C' => 10,
            ],
        ]);

        $action = app(UpdateStockAsync::class);

        $action->update($stocks);

        $this->assertEquals(0, Stock::query()->where('update', '=', true)->count());
    }
}
