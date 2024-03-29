<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoStock\Actions\UpdateMsiStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class UpdateMsiStockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config()->set('magento-stock.msi', true);

        MagentoStock::query()
            ->create([
                'sku' => '::sku::',
                'msi_stock' => json_decode('{"A": 4, "B": 0, "C": 0}', true),
                'msi_status' => json_decode('{"A": true, "B": false, "C": true}', true),
                'in_stock' => true,
            ]);

        $sources = [
            'items' => [
                [
                    'source_code' => 'A',
                ],
                [
                    'source_code' => 'B',
                ],
            ],
        ];

        Http::fake([
            '*rest/all/V1/inventory/sources*' => Http::response($sources),
        ]);

        UpdateMsiStock::bind();
    }

    public function test_it_updates_msi_stock(): void
    {
        Http::fake([
            '*/rest/all/V1/inventory/source-items*' => Http::response(),
        ]);

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->first();

        $expectedPayload = [
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

        $action->update($stock);

        Http::assertSentInOrder([
            function (Request $request) {
                return $request->url() == 'magento/rest/all/V1/inventory/sources?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1';
            },
            function (Request $request) use ($expectedPayload) {
                return $request->data() == $expectedPayload;
            },
        ]);

        Event::assertDispatched(StockUpdatedEvent::class);
    }

    public function test_it_updates_msi_stock_async(): void
    {
        config()->set('magento-stock.async', true);

        Http::fake([
            '*/rest/all/async/V1/inventory/source-items*' => Http::response(),
        ]);

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->first();

        $expectedPayload = [
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

        $action->update($stock);

        Http::assertSentInOrder([
            function (Request $request) {
                return $request->url() == 'magento/rest/all/V1/inventory/sources?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1';
            },
            function (Request $request) use ($expectedPayload) {
                return $request->data() == $expectedPayload;
            },
        ]);

        Event::assertDispatched(StockUpdatedEvent::class);
    }

    public function test_it_stops_when_stockdata_is_empty(): void
    {
        Http::fake([
            '*/rest/all/V1/inventory/source-items*' => Http::response(),
        ]);

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->first();

        $stock->msi_stock = null;

        $stock->msi_status = null;

        $action->update($stock);

        Event::assertNotDispatched(StockUpdatedEvent::class);

        Http::assertNotSent(function (Request $request): bool {
            return $request->method() === 'POST' &&
                $request->url() === 'magento/rest/all/async/V1/inventory/source-items';
        });
    }

    public function test_it_logs_error(): void
    {
        Http::fake([
            '*/rest/all/V1/inventory/source-items*' => Http::response('::error::', 403),
        ]);

        $this->expectException(UpdateException::class);

        config()->set('magento-stock.fails.count', 1);

        /** @var UpdateMsiStock $action */
        $action = app(UpdateMsiStock::class);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->first();

        $action->update($stock);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->first();

        $this->assertEquals(1, $stock->fail_count);
    }
}
