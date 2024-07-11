<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesMsiStockAsync;
use JustBetter\MagentoStock\Models\Stock;

class UpdateMsiStockAsync implements UpdatesMsiStockAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected GetMsiSources $msiSources
    ) {
    }

    public function update(Collection $stocks): void
    {
        $sources = $this->msiSources->get();

        $payload = $stocks
            ->map(fn (Stock $stock) => [
                'product' => [
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
        app()->singleton(UpdatesMsiStockAsync::class, static::class);
    }
}
