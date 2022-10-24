<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\DispatchComparisonsJob;

class CompareStockCommand extends Command
{
    protected $signature = 'stock:compare';

    protected $description = 'Dispatch job to compare stocks';

    public function handle(): int
    {
        DispatchComparisonsJob::dispatch();

        return static::SUCCESS;
    }
}
