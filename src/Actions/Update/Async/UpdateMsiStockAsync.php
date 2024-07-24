<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesMsiStockAsync;

class UpdateMsiStockAsync implements UpdatesMsiStockAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected GetMsiSources $msiSources
    ) {}

    public function update(Collection $stocks): void
    {
        $availableSources = $this->msiSources->get();

        $payload = [];

        foreach ($stocks as $stockIndex => $stock) {
            $sourceItems = [];

            foreach ($stock->msi_stock ?? [] as $location => $quantity) {
                if (! in_array($location, $availableSources)) {
                    continue;
                }

                $status = $stock->msi_status[$location] ?? $quantity > 0;

                $sourceItems[] = [
                    'sku' => $stock->sku,
                    'source_code' => $location,
                    'quantity' => $quantity,
                    'status' => $status ? '1' : '0',
                ];
            }

            if ($sourceItems === []) {
                unset($stocks[$stockIndex]);

                continue;
            }

            $payload[] = ['sourceItems' => $sourceItems];
        }

        $this->magentoAsync
            ->subjects($stocks->all())
            ->postBulk('inventory/source-items', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMsiStockAsync::class, static::class);
    }
}
