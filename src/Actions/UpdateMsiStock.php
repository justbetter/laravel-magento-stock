<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoStock\Contracts\UpdatesStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;

class UpdateMsiStock implements UpdatesStock
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(MagentoStock $model): void
    {
        $payload = [];

        $availableSources = $this->getAvailableSources();

        foreach ($model->msi_stock as $location => $quantity) {
            if (! in_array($location, $availableSources)) {
                continue;
            }

            $status = $model->msi_status[$location] ?? $quantity > 0;

            $payload[] = [
                'sku' => $model->sku,
                'source_code' => $location,
                'quantity' => $quantity,
                'status' => $status ? '1' : '0',
            ];
        }

        $payload = [
            'sourceItems' => $payload,
        ];

        $response = $this->magento->post('inventory/source-items', $payload);

        try {
            $response->throw();

            $model->update(['last_updated' => now()]);

            event(new StockUpdatedEvent($model));

            activity()
                ->withProperties($payload)
                ->performedOn($model)
                ->log("$model->sku: Updated MSI Stock in Magento");
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

    protected function getAvailableSources(): array
    {
        return Cache::remember('msi:sources', now()->addMonth(), function () {
            $response = $this->magento
                ->get('inventory/sources', SearchCriteria::make()->paginate(1, 50)->get())
                ->throw();

            return $response->collect('items')
                ->pluck('source_code')
                ->toArray();
        });
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesStock::class, static::class);
    }
}
