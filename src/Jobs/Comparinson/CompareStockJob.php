<?php

namespace JustBetter\MagentoStock\Jobs\Comparinson;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesStock;

class CompareStockJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(public string $sku)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(ComparesStock $stock): void
    {
        $stock->compare($this->sku);
    }

    public function uniqueId(): string
    {
        return $this->sku;
    }
}
