<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesSimpleStockAsync;
use JustBetter\MagentoStock\Models\Stock;

class UpdateSimpleStockAsync implements UpdatesSimpleStockAsync
{
    public function __construct(protected MagentoAsync $magentoAsync) {}

    public function update(Collection $stocks): void
    {
        $payload = $stocks
            ->map(fn (Stock $stock) => [
                'product' => [
                    'sku' => $stock->sku,
                    'extension_attributes' => [
                        'stock_item' => [
                            'is_in_stock' => $stock->in_stock,
                            'qty' => $stock->quantity,
                        ],
                    ],
                ],
            ])
            ->toArray();

        $this->magentoAsync
            ->subjects($stocks->all())
            ->postBulk('products', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesSimpleStockAsync::class, static::class);
    }
}
