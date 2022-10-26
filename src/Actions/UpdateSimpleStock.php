<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Http\Client\RequestException;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Contracts\UpdatesStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;

class UpdateSimpleStock implements UpdatesStock
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(MagentoStock $model): void
    {
        $payload = [
            'product' => [
                'extension_attributes' => [
                    'stock_item' => [
                        'is_in_stock' => $model->in_stock,
                        'qty' => $model->quantity,
                    ],
                ],
            ],
        ];

        $response = $this->magento->put("products/$model->sku", $payload);

        try {
            $response->throw();

            $model->update(['last_updated' => now()]);

            event(new StockUpdatedEvent($model));

            activity()
                ->performedOn($model)
                ->withProperties($payload)
                ->log("$model->sku: Updated quantity in Magento to $model->quantity");
        } catch (RequestException $exception) {
            $model->registerError();

            throw new UpdateException(
                $model->sku,
                "Failed to update simple stock for $model->sku",
                ['payload' => $payload, 'response' => $exception->response->body()],
                $exception
            );
        }
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesStock::class, static::class);
    }
}
