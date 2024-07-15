<?php

namespace JustBetter\MagentoStock\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesMsiStock;
use JustBetter\MagentoStock\Models\Stock;

class UpdateMsiStock implements UpdatesMsiStock
{
    public function __construct(
        protected Magento $magento,
        protected GetMsiSources $msiSources
    ) {}

    public function update(Stock $stock): void
    {
        $sourceItems = [];

        $availableSources = $this->msiSources->get();

        if ($stock->msi_stock === null) {
            return;
        }

        foreach ($stock->msi_stock as $location => $quantity) {
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

        $payload = [
            'sourceItems' => $sourceItems,
        ];

        $response = $this->magento->post('inventory/source-items', $payload)
            ->onError(function (Response $response) use ($stock, $payload): void {
                $stock->failed();

                activity()
                    ->on($stock)
                    ->useLog('error')
                    ->withProperties([
                        'payload' => $payload,
                        'response' => $response->body(),
                    ])
                    ->log('Failed to update MSI stock');
            });

        if ($response->successful()) {
            activity()
                ->withProperties($payload)
                ->performedOn($stock)
                ->log("$stock->sku: Updated MSI Stock in Magento");
        }
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMsiStock::class, static::class);
    }
}
