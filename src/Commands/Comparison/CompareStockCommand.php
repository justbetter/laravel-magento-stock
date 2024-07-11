<?php

namespace JustBetter\MagentoStock\Commands\Comparison;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;

class CompareStockCommand extends Command
{
    protected $signature = 'magento-stock:compare';

    protected $description = 'Compare stocks between Magento and this package';

    public function handle(): int
    {
        DispatchComparisonsJob::dispatch();

        return static::SUCCESS;
    }
}
