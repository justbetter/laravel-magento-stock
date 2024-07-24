<?php

namespace JustBetter\MagentoStock\Tests\Actions\Comparison;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\Comparison\CompareMsiStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CompareMsiStockTest extends TestCase
{
    #[Test]
    public function it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnFalse()->once();
        });

        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => [],
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareMsiStock $action */
        $action = app(CompareMsiStock::class);

        $action->compare($stock);
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function quantity_equals(array $magentoStocks, array $localStocks, bool $shouldUpdate): void
    {
        Event::fake();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        Http::fake([
            '*inventory/source-items*' => Http::response([
                'items' => $magentoStocks,
            ]),
        ]);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => $localStocks,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareMsiStock $action */
        $action = app(CompareMsiStock::class);

        $action->compare($stock);

        /** @var Stock $model */
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
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10,
                    ],
                    [
                        'source_code' => 'B',
                        'quantity' => 10,
                    ],
                ],
                'localStocks' => ['A' => 10, 'B' => 0],
                'shouldUpdate' => true,
            ],
            'equals' => [
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10,
                    ],
                    [
                        'source_code' => 'B',
                        'quantity' => 10,
                    ],
                ],
                'localStocks' => ['A' => 10, 'B' => 10],
                'shouldUpdate' => false,
            ],
            'ignores extra local stock' => [
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10,
                    ],
                ],
                'localStocks' => [],
                'shouldUpdate' => false,
            ],
        ];
    }
}
