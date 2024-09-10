<?php

namespace JustBetter\MagentoStock\Jobs\Comparison;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoClient\Jobs\Middleware\AvailableMiddleware;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesStock;
use JustBetter\MagentoStock\Models\Stock;

class CompareStockJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Stock $stock)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(ComparesStock $stock): void
    {
        $stock->compare($this->stock);
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

    public function middleware(): array
    {
        return [
            new AvailableMiddleware,
        ];
    }
}
