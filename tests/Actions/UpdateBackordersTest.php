<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateBackorders;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class UpdateBackordersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('magento-stock.backorders', true);
    }

    public function test_it_updates_backorders(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response(),
        ]);

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

    public function test_it_updates_backorders_async(): void
    {
        config()->set('magento-stock.async', true);

        Http::fake([
            'magento/rest/all/async/V1/products/%3A%3Asku%3A%3A' => Http::response(),
        ]);

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

    public function test_it_logs_backorders_error(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response('::error::', 500),
        ]);

        $this->expectException(UpdateException::class);

        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => true,
                'in_stock' => true,
                'magento_backorders_enabled' => false,
            ]);

        /** @var UpdateBackorders $action */
        $action = app(UpdateBackorders::class);

        $action->update($model);

        /** @var MagentoStock $model */
        $model = Stock::query()
            ->where('sku', '::sku::')
            ->first();

        $this->assertCount(1, $model->errors);
        $this->assertTrue(str_contains($model->errors->first()->details ?? '', '::error::'));
    }

    public function test_it_stops_when_config_backorders_is_disabled(): void
    {
        Http::fake();

        config()->set('magento-stock.backorders', false);

        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => true,
                'in_stock' => true,
                'magento_backorders_enabled' => false,
            ]);

        /** @var UpdateBackorders $action */
        $action = app(UpdateBackorders::class);

        $action->update($model);

        Http::assertNothingSent();
    }

    public function test_it_stops_when_backorders_unchanged(): void
    {
        Http::fake();

        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => true,
                'in_stock' => true,
                'magento_backorders_enabled' => true,
            ]);

        /** @var UpdateBackorders $action */
        $action = app(UpdateBackorders::class);

        $action->update($model);

        Http::assertNothingSent();
    }
}
