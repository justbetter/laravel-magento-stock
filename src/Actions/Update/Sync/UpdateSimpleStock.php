<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesSimpleStock;
use JustBetter\MagentoStock\Models\Stock;

class UpdateSimpleStock implements UpdatesSimpleStock
{
    public function __construct(protected Magento $magento) {}

    public function update(Stock $stock): void
    {
        $payload = [
            'product' => [
                'extension_attributes' => [
                    'stock_item' => [
                        'is_in_stock' => $stock->in_stock,
                        'qty' => $stock->quantity,
                    ],
                ],
            ],
        ];

        $response = $this->magento
            ->put('products/'.urlencode($stock->sku), $payload)
            ->onError(function (Response $response) use ($stock, $payload): void {
                $stock->failed();

                activity()
                    ->on($stock)
                    ->useLog('error')
                    ->withProperties([
                        'payload' => $payload,
                        'response' => $response->body(),
                    ])
                    ->log('Failed to update stock');
            });

        if ($response->successful()) {
            activity()
                ->withProperties($payload)
                ->performedOn($stock)
                ->log(sprintf('Updated Stock for %s in Magento to ', $stock->sku).($stock->in_stock ? 'in stock' : 'out of stock').(' with quantity '.$stock->quantity));
        }
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesSimpleStock::class, static::class);
    }
}
