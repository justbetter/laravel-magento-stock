<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoStock\Contracts\RetrievesStock;
use JustBetter\MagentoStock\Models\MagentoStock;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class RetrieveStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public int $timeout = 3600;

    public function __construct(
        public string $sku,
        public bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(): void
    {
        /** @var RetrievesStock $retriever */
        $retriever = app(config('magento-stock.retriever.stock'));

        $stock = $retriever->retrieve($this->sku);

        if ($stock === null) {
            MagentoStock::query()
                ->where('sku', '=', $this->sku)
                ->update(['retrieve' => false]);

            return;
        }

        ProcessStockJob::dispatch($stock, $this->forceUpdate);
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
        /** @var ?MagentoStock $model */
        $model = MagentoStock::query()
            ->firstWhere('sku', '=', $this->sku);

        activity()
            ->when($model !== null, fn (ActivityLogger $logger) => $logger->on($model)) /** @phpstan-ignore-line */
            ->useLog('error')
            ->withProperties([
                'message' => $exception->getMessage(),
            ])
            ->log('Failed while retrieving for sku '.$this->sku);
    }
}
