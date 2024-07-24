<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Async;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateBackordersAsync;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateBackordersAsyncTest extends TestCase
{
    #[Test]
    public function it_updates_backorders(): void
    {
        Magento::fake();
        Http::fake([
            'magento/rest/all/async/bulk/V1/products' => Http::response([
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

        $stocks = collect();

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_1::',
            'backorders' => Backorders::Backorders,
        ]);

        $stocks[] = Stock::query()->create([
            'sku' => '::sku_2::',
            'backorders' => Backorders::NoBackorders,
        ]);

        /** @var UpdateBackordersAsync $action */
        $action = app(UpdateBackordersAsync::class);
        $action->update($stocks);

        Http::assertSent(function (Request $request) {
            return $request->data() === [
                [
                    'product' => [
                        'extension_attributes' => [
                            'stock_item' => [
                                'use_config_backorders' => false,
                                'backorders' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'product' => [
                        'extension_attributes' => [
                            'stock_item' => [
                                'use_config_backorders' => false,
                                'backorders' => 0,
                            ],
                        ],
                    ],
                ],
            ];
        });
    }
}
