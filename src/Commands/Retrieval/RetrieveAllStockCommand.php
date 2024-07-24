<?php

namespace JustBetter\MagentoStock\Commands\Retrieval;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;

class RetrieveAllStockCommand extends Command
{
    protected $signature = 'magento-stock:retrieve-all {from?}';

    protected $description = 'Retrieve all (modified) stock';

    public function handle(): int
    {
        /** @var ?string $from */
        $from = $this->argument('from');

        $date = $from !== null
            ? Carbon::parse($from)
            : null;

        RetrieveAllStockJob::dispatch($date);

        return static::SUCCESS;
    }
}
