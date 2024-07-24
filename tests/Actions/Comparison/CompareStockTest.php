<?php

namespace JustBetter\MagentoStock\Tests\Actions\Comparison;

use JustBetter\MagentoStock\Actions\Comparison\CompareStock;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesMsiStock;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesSimpleStock;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeMsiRepository;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CompareStockTest extends TestCase
{
    #[Test]
    public function it_calls_simple_stock_compare_action(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);

        $this->mock(ComparesSimpleStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('compare')->once();
        });

        $this->mock(ComparesMsiStock::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('compare');
        });

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
            ]);

        /** @var CompareStock $action */
        $action = app(CompareStock::class);
        $action->compare($model);
    }

    #[Test]
    public function it_calls_msi_stock_compare_action(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);

        $this->mock(ComparesSimpleStock::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('compare');
        });

        $this->mock(ComparesMsiStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('compare')->once();
        });

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
            ]);

        /** @var CompareStock $action */
        $action = app(CompareStock::class);
        $action->compare($model);
    }
}
