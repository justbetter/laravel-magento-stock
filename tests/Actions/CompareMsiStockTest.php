<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Actions\CompareMsiStock;
use JustBetter\MagentoStock\Actions\CompareSimpleStock;
use JustBetter\MagentoStock\Contracts\ComparesStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\MagentoStock;
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

        $this->assertTrue(is_a(app(ComparesStock::class), CompareMsiStock::class));
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
                'items' => $magentoStocks
            ]),
        ]);

        MagentoStock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => $localStocks,
            'in_stock' => true,
            'update' => false,
        ]);

        /** @var CompareMsiStock $action */
        $action = app(CompareMsiStock::class);

        $action->compare('::sku::');

        /** @var MagentoStock $model */
        $model = MagentoStock::query()->first();
        $this->assertEquals($shouldUpdate, $model->update);

        if ($shouldUpdate) {
            Event::assertDispatched(DifferenceDetectedEvent::class);
        } else {
            Event::assertNotDispatched(DifferenceDetectedEvent::class);
        }
    }

    public function dataProvider(): array
    {
        return [
            'differs' => [
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10
                    ],
                    [
                        'source_code' => 'B',
                        'quantity' => 10
                    ]
                ],
                'localStocks' => ['A' => 10, 'B' => 0],
                'shouldUpdate' => true,
            ],
            'equals' => [
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10
                    ],
                    [
                        'source_code' => 'B',
                        'quantity' => 10
                    ]
                ],
                'localStocks' => ['A' => 10, 'B' => 10],
                'shouldUpdate' => false,
            ],
            'ignores extra local stock' => [
                'magentoStocks' => [
                    [
                        'source_code' => 'A',
                        'quantity' => 10
                    ]
                ],
                'localStocks' => [],
                'shouldUpdate' => false,
            ]
        ];
    }
}
