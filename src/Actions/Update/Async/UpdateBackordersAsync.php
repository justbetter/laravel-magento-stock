<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesBackordersAsync;
use JustBetter\MagentoStock\Models\Stock;

class UpdateBackordersAsync implements UpdatesBackordersAsync
{
    public function __construct(protected MagentoAsync $magentoAsync)
    {
    }

    public function update(Collection $stocks): void
    {
        $payload = $stocks
            ->map(fn (Stock $stock) => [
                'product' => [
                    'extension_attributes' => [
                        'stock_item' => [
                            'use_config_backorders' => false,
                            'backorders' => $stock->backorders->value
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
        app()->singleton(UpdatesBackordersAsync::class, static::class);
    }
}
