<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Async;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateMsiStockAsync;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateMsiStockAsyncTest extends TestCase
{
    #[Test]
    public function it_updates_msi_async(): void
    {
        Magento::fake();
        Http::fake([
            'magento/rest/all/async/bulk/V1/inventory/source-items' => Http::response([
                'bulk_uuid' => '::uuid::',
                'request_items' => [
                    [
                        'id' => 0,
                        'status' => 'accepted',
                    ],
                    [
                        'id' => 1,
                        'status' => 'accepted',
                    ],
                ],
            ]),
        ])->preventStrayRequests();

        $this->mock(GetMsiSources::class, function (MockInterface $mock): void {
            $mock->shouldReceive('get')->andReturn(['A', 'B']);
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

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_2::',
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

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_3::',
            'msi_status' => [],
            'msi_stock' => [],
        ]);

        /** @var UpdateMsiStockAsync $action */
        $action = app(UpdateMsiStockAsync::class);

        $action->update($stocks);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                [
                    'sourceItems' => [
                        [
                            'sku' => '::sku_1::',
                            'source_code' => 'A',
                            'quantity' => 10,
                            'status' => '1',
                        ],
                        [
                            'sku' => '::sku_1::',
                            'source_code' => 'B',
                            'quantity' => 10,
                            'status' => '1',
                        ],
                    ],
                ],
                [
                    'sourceItems' => [
                        [
                            'sku' => '::sku_2::',
                            'source_code' => 'A',
                            'quantity' => 10,
                            'status' => '1',
                        ],
                        [
                            'sku' => '::sku_2::',
                            'source_code' => 'B',
                            'quantity' => 10,
                            'status' => '1',
                        ],
                    ],
                ],
            ];
        });
    }
}
