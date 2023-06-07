<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Http\Client\RequestException;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Contracts\UpdatesBackorders;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;

class UpdateBackorders implements UpdatesBackorders
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(MagentoStock $model): void
    {
        if (
            ! config('magento-stock.backorders') ||
            $model->magento_backorders_enabled == $model->backorders
        ) {
            return;
        }

        $payload = [
            'product' => [
                'extension_attributes' => [
                    'stock_item' => [
                        'use_config_backorders' => false,
                        'backorders' => $model->backorders ? 1 : 0,
                    ],
                ],
            ],
        ];

        if (config('magento-stock.async')) {
            $response = $this->magento->putAsync('products/'.urlencode($model->sku), $payload);
        } else {
            $response = $this->magento->put('products/'.urlencode($model->sku), $payload);
        }

        try {
            $response->throw();

            $model->update([
                'magento_backorders_enabled' => $model->backorders,
                'last_updated' => now(),
            ]);

            activity()
                ->performedOn($model)
                ->log("$model->sku: Set backorder in Magento to: ".($model->backorders ? 'Enabled' : 'Disabled'));
        } catch (RequestException $exception) {
            $model->registerError();

            throw new UpdateException(
                $model->sku,
                'Failed to update MSI stock for '.$model->sku,
                ['payload' => $payload, 'response' => $exception->response->body()],
                $exception
            );
        }
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesBackorders::class, static::class);
    }
}
