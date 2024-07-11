<?php

namespace JustBetter\MagentoStock\Jobs\Update;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesStock;
use JustBetter\MagentoStock\Models\Stock;
use Throwable;

class UpdateStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Stock $stock)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(UpdatesStock $stock): void
    {
        $stock->update($this->stock);
    }

    public function uniqueId(): int
    {
        return $this->stock->id;
    }

    public function tags(): array
    {
        return [
            $this->stock->sku,
        ];
    }

    /** @codeCoverageIgnore */
    public function failed(Throwable $exception): void
    {
        activity()
            ->on($this->stock)
            ->useLog('error')
            ->log('Failed to update stock: '.$exception->getMessage());
    }
}
