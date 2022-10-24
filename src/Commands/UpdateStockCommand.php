<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\UpdateStockJob;

class UpdateStockCommand extends Command
{
    protected $signature = 'magento:stock:update {sku}';

    protected $description = 'Dispatch job to update stock';

    public function handle(): int
    {
        $this->info('Dispatching...');

        /** @var string $sku */
        $sku = $this->argument('sku');

        UpdateStockJob::dispatch($sku);

        $this->info('Done!');

        return static::SUCCESS;
    }
}
