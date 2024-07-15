<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\Comparison\CompareSimpleSimpleStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class CompareSimpleStockTest extends TestCase
{
    public function test_it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnFalse()->once();
        });

        /** @var \JustBetter\MagentoStock\Tests\Actions\CompareSimpleStock $action */
        $action = app(CompareSimpleSimpleStock::class);

        $action->compare('::sku::');
    }

    /** @dataProvider dataProvider */
    public function test_quantity_equals(int $magentoQty, int $localQty, bool $shouldUpdate): void
    {
        Event::fake();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response([
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => $magentoQty,
                    ],
                ],
            ]),
        ]);

        Stock::query()->create([
            'sku' => '::sku::',
            'quantity' => $localQty,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var \JustBetter\MagentoStock\Tests\Actions\CompareSimpleStock $action */
        $action = app(CompareSimpleSimpleStock::class);

        $action->compare('::sku::');

        /** @var \JustBetter\MagentoStock\Tests\Actions\MagentoStock $model */
        $model = Stock::query()->first();
        $this->assertEquals($shouldUpdate, $model->update);

        if ($shouldUpdate) {
            Event::assertDispatched(DifferenceDetectedEvent::class);
        } else {
            Event::assertNotDispatched(DifferenceDetectedEvent::class);
        }
    }

    public static function dataProvider(): array
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