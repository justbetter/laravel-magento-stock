<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\Comparison\CompareMsiStock;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesSimpleStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class CompareMsiStockTest extends TestCase
{
    public function test_it_binds(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('exists');
        });

        CompareMsiStock::bind();

        $this->assertTrue(is_a(app(ComparesSimpleStock::class), CompareMsiStock::class));
    }

    public function test_it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnFalse()->once();
        });

        /** @var CompareMsiStock $action */
        $action = app(CompareMsiStock::class);

        $action->compare('::sku::');
    }

    /** @dataProvider dataProvider */
    public function test_quantity_equals(array $magentoStocks, array $localStocks, bool $shouldUpdate): void
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

        Stock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => $localStocks,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareMsiStock $action */
        $action = app(CompareMsiStock::class);

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
