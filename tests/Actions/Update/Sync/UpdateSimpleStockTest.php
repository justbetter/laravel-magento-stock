<?php

namespace JustBetter\MagentoStock\Tests\Actions\Update\Sync;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateSimpleStock;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateSimpleStockTest extends TestCase
{
    #[Test]
    public function it_updates_simple_stock(): void
    {
        Event::fake();
        Magento::fake();
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Ask%2Fu%3A%3A' => Http::response(),
        ]);

        $model = Stock::query()
            ->create([
                'sku' => '::sk/u::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
            ]);

        /** @var UpdateSimpleStock $action */
        $action = app(UpdateSimpleStock::class);

        $action->update($model);

        Http::assertSent(function (Request $request): bool {
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
    }

    #[Test]
    public function it_registers_failure(): void
    {
        Event::fake();
        Magento::fake();
        Http::fake([
            'magento/rest/all/V1/products/%3A%3Asku%3A%3A' => Http::response(null, 500),
        ]);

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
            ]);

        /** @var UpdateSimpleStock $action */
        $action = app(UpdateSimpleStock::class);

        $action->update($model);

        $model->refresh();

        $this->assertEquals(1, $model->fail_count);
    }
}
