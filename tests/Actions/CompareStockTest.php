<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\CompareStock;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class CompareStockTest extends TestCase
{
    public function test_it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnFalse()->once();
        });

        /** @var CompareStock $action */
        $action = app(CompareStock::class);

        $action->compare('::sku::');
    }

    /** @dataProvider dataProvider */
    public function test_quantity_equals(int $magentoQty, int $localQty, bool $shouldUpdate): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        Http::fake([
            '::magento::/rest/all/V1/products/::sku::' => Http::response([
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => $magentoQty,
                    ],
                ],
            ]),
        ]);

        MagentoStock::query()->create([
            'sku' => '::sku::',
            'quantity' => $localQty,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareStock $action */
        $action = app(CompareStock::class);

        $action->compare('::sku::');

        /** @var MagentoStock $model */
        $model = MagentoStock::query()->first();
        $this->assertEquals($shouldUpdate, $model->update);
    }

    public function dataProvider(): array
    {
        return [
            'differs' => [
                'magentoQty' => 100,
                'localQty' => 1,
                'shouldUpdate' => true,
            ],
            'equals' => [
                'magentoQty' => 100,
                'localQty' => 100,
                'shouldUpdate' => false,
            ],
        ];
    }
}
