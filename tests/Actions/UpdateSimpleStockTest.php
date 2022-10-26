<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoStock\Actions\UpdateSimpleStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class UpdateSimpleStockTest extends TestCase
{
    public function test_it_updates_simple_stock(): void
    {
        Event::fake();
        Http::fake([
            'rest/all/V1/products/::sku::' => Http::response(),
        ]);

        $model = MagentoStock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
            ]);

        /** @var UpdateSimpleStock $action */
        $action = app(UpdateSimpleStock::class);

        $action->update($model);

        Http::assertSent(function (Request $request) {
            $expectedData = [
                'product' => [
                    'extension_attributes' => [
                        'stock_item' => [
                            'is_in_stock' => true,
                            'qty' => 10,
                        ],
                    ],
                ],
            ];

            return $request->data() == $expectedData;
        });

        Event::assertDispatched(StockUpdatedEvent::class);
    }

    public function test_it_logs_error(): void
    {
        Http::fake([
            'rest/all/V1/products/::sku::' => Http::response('::error::', 500),
        ]);

        $this->expectException(UpdateException::class);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
            ]);

        /** @var UpdateSimpleStock $action */
        $action = app(UpdateSimpleStock::class);

        $action->update($stock);

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()
            ->where('sku', '::sku::')
            ->first();

        $this->assertCount(1, $stock->errors);

        /** @var Error $error */
        $error = $stock->errors->first();
        $this->assertEquals('::error::', $error->details);
    }
}
