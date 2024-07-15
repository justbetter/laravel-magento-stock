<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Sync;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateBackorders;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateBackordersTest extends TestCase
{
    #[Test]
    public function it_updates_backorders(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response(),
        ]);

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'backorders' => true,
            ]);

        /** @var UpdateBackorders $action */
        $action = app(UpdateBackorders::class);

        $action->update($model);

        Http::assertSent(function (Request $request) {
            $expectedData = [
                'product' => [
                    'extension_attributes' => [
                        'stock_item' => [
                            'use_config_backorders' => false,
                            'backorders' => 1,
                        ],
                    ],
                ],
            ];

            return $request->data() == $expectedData;
        });
    }

    #[Test]
    public function it_registers_failure(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response(null, 500),
        ]);

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'backorders' => true,
            ]);

        /** @var UpdateBackorders $action */
        $action = app(UpdateBackorders::class);

        $action->update($model);

        $model->refresh();

        $this->assertEquals(1, $model->fail_count);
        $this->assertNotNull($model->last_failed);
    }
}
