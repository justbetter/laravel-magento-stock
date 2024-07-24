<?php

namespace JustBetter\MagentoStock\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesStock;
use JustBetter\MagentoStock\Models\Stock;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class RetrieveStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public string $sku,
        public bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(RetrievesStock $stock): void
    {
        $stock->retrieve($this->sku, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->sku;
    }

    public function tags(): array
    {
        return [
            $this->sku,
        ];
    }

    /** @codeCoverageIgnore */
    public function failed(Throwable $exception): void
    {
        /** @var ?Stock $model */
        $model = Stock::query()->firstWhere('sku', '=', $this->sku);

        activity()
            ->when($model, function (ActivityLogger $logger, Stock $stock): ActivityLogger {
                return $logger->on($stock);
            })
            ->useLog('error')
            ->log('Failed to retrieve stock: '.$exception->getMessage());
    }
}
