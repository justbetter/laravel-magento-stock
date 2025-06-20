<?php

namespace JustBetter\MagentoStock\Tests\Actions\Comparison;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\Comparison\CompareSimpleStock;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeBackorderRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CompareSimpleStockTest extends TestCase
{
    #[Test]
    public function it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnFalse()->once();
        });

        /** @var CompareSimpleStock $action */
        $action = app(CompareSimpleStock::class);

        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'quantity' => 0,
            'in_stock' => true,
            'update' => false,
        ]);

        $action->compare($stock);
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function it_checks_if_equal(int $magentoQty, int $localQty, int $magentoBackorders, Backorders $localBackorders, bool $shouldUpdate): void
    {
        Event::fake();
        config()->set('magento-stock.repository', FakeBackorderRepository::class);

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response([
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => $magentoQty,
                        'backorders' => $magentoBackorders,
                    ],
                ],
            ]),
        ]);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'quantity' => $localQty,
            'backorders' => $localBackorders,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareSimpleStock $action */
        $action = app(CompareSimpleStock::class);

        $action->compare($stock);

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
                'magentoBackorders' => 0,
                'localBackorders' => Backorders::NoBackorders,
                'shouldUpdate' => true,
            ],
            'equals' => [
                'magentoQty' => 100,
                'localQty' => 100,
                'magentoBackorders' => 0,
                'localBackorders' => Backorders::NoBackorders,
                'shouldUpdate' => false,
            ],
            'backorders' => [
                'magentoQty' => 100,
                'localQty' => 100,
                'magentoBackorders' => 0,
                'localBackorders' => Backorders::Backorders,
                'shouldUpdate' => true,
            ],
        ];
    }
}
