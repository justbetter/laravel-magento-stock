<?php

namespace JustBetter\MagentoStock\Listeners;

use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Listeners\BulkOperationStatusListener as BaseBulkOperationStatusListener;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Models\Stock;

class BulkOperationStatusListener extends BaseBulkOperationStatusListener
{
    protected string $model = Stock::class;

    public function execute(BulkOperation $operation): void
    {
        /** @var Stock $stock */
        $stock = $operation->subject;

        if ($operation->status === OperationStatus::Complete) {
            $stock->update(['last_updated' => now()]);

            event(new StockUpdatedEvent($stock));

            return;
        }

        activity()
            ->useLog('error')
            ->withProperties([
                'status' => $operation->status->name ?? 'unknown',
                'response' => $operation->response,
            ])
            ->log('Failed to update Magento stock for SKU: '.$stock->sku);
    }
}
