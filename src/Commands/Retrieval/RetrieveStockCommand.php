<?php

namespace JustBetter\MagentoStock\Commands\Retrieval;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;

class RetrieveStockCommand extends Command
{
    protected $signature = 'magento-stock:retrieve {sku}';

    protected $description = 'Retrieve stock for a specific SKU';

    public function handle(): int
    {
        /** @var string $sku */
        $sku = $this->argument('sku');

        RetrieveStockJob::dispatch($sku);

        return static::SUCCESS;
    }
}
