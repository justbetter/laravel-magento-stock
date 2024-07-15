<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Sync;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateMsiStock;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateMsiStockTest extends TestCase
{
    #[Test]
    public function it_updates_msi_stock(): void
    {
        Magento::fake();
        Http::fake([
            'magento/rest/all/V1/inventory/source-items*' => Http::response(),
        ]);

        $this->mock(GetMsiSources::class, function (MockInterface $mock): void {
            $mock->shouldReceive('get')->andReturn(['A', 'B']);
        });

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => [
                'A' => 4,
                'B' => 0,
                'C' => 100,
            ],
            'msi_status' => [
                'A' => true,
                'B' => false,
                'C' => false,
            ]
        ]);

        $action->update($stock);

        Http::assertSent(function (Request $request): bool {
            return $request->data() == [
                    'sourceItems' => [
                        [
                            'sku' => '::sku::',
                            'source_code' => 'A',
                            'quantity' => 4,
                            'status' => '1',
                        ],
                        [
                            'sku' => '::sku::',
                            'source_code' => 'B',
                            'quantity' => 0,
                            'status' => '0',
                        ],
                    ],
                ];
        });
    }

    #[Test]
    public function it_does_nothing_without_data(): void
    {
        Http::fake();

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
        ]);

        $action->update($stock);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_registers_error(): void
    {
        Magento::fake();
        Http::fake([
            'magento/rest/all/V1/inventory/source-items*' => Http::response(null, 500),
        ]);

        $this->mock(GetMsiSources::class, function (MockInterface $mock): void {
            $mock->shouldReceive('get')->andReturn(['A', 'B']);
        });

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'msi_stock' => [
                'A' => 4,
                'B' => 0,
                'C' => 100,
            ],
            'msi_status' => [
                'A' => true,
                'B' => false,
                'C' => false,
            ]
        ]);

        $action->update($stock);

        $stock->refresh();

        $this->assertEquals(1, $stock->fail_count);
        $this->assertNotNull($stock->last_failed);
    }

}
