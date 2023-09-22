<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoStock\Contracts\ProcessesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\MagentoStock;
use Throwable;

class ProcessStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public function __construct(
        public StockData $stock,
        public bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(ProcessesStock $processor): void
    {
        $processor->process($this->stock, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->stock->sku;
    }

    public function tags(): array
    {
        return [
            $this->stock->sku,
        ];
    }

    public function failed(Throwable $exception): void
    {
        $log = Error::log()
            ->withGroup('Stock')
            ->withMessage("Failed while processing sku {$this->stock->sku}")
            ->fromThrowable($exception);

        $stockModel = MagentoStock::query()->where('sku', $this->stock->sku)->first();

        if ($stockModel !== null) {
            $log->withModel($stockModel);
        }

        $log->save();
    }
}
