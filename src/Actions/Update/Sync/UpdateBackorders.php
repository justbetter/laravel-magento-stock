<?php

namespace JustBetter\MagentoStock\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesBackorders;
use JustBetter\MagentoStock\Models\Stock;

class UpdateBackorders implements UpdatesBackorders
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(Stock $stock): void
    {
        $payload = [
            'product' => [
                'extension_attributes' => [
                    'stock_item' => [
                        'use_config_backorders' => false,
                        'backorders' => $stock->backorders->value
                    ],
                ],
            ],
        ];

        $response = $this->magento
            ->put('products/'.urlencode($stock->sku), $payload)
            ->onError(function (Response $response) use ($stock, $payload) {
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
                ->performedOn($stock)
                ->log("$stock->sku: Set backorder in Magento to: {$stock->backorders->name}");
        }
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesBackorders::class, static::class);
    }
}
