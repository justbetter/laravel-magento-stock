<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\RetrieveStockJob;

class RetrieveStockCommand extends Command
{
    protected $signature = 'magento:stock:retrieve {sku}';

    protected $description = 'Dispatch job to retrieve stock';

    public function handle(): int
    {
        $this->info('Dispatching...');

        /** @var string $sku */
        $sku = $this->argument('sku');

        RetrieveStockJob::dispatch($sku);

        $this->info('Done!');

        return static::SUCCESS;
    }
}
