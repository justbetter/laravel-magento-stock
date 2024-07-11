<?php

namespace JustBetter\MagentoStock\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoStock\Contracts\Retrieval\SavesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\Stock;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class SaveStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public StockData $data,
        public bool $forceUpdate
    ) {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(SavesStock $stock): void
    {
        $stock->save($this->data, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->data['sku'];
    }

    public function tags(): array
    {
        return [
            $this->data['sku'],
        ];
    }

    /** @codeCoverageIgnore */
    public function failed(Throwable $exception): void
    {
        /** @var ?Stock $model */
        $model = Stock::query()->firstWhere('sku', '=', $this->data['sku']);

        activity()
            ->when($model !== null, fn (ActivityLogger $logger): ActivityLogger => $logger->on($model)) /** @phpstan-ignore-line */
            ->useLog('error')
            ->log('Failed to save stock: '.$exception->getMessage());
    }
}
