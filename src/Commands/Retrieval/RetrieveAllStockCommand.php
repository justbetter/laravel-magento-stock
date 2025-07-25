<?php

namespace JustBetter\MagentoStock\Commands\Retrieval;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;

class RetrieveAllStockCommand extends Command
{
    protected $signature = 'magento-stock:retrieve-all {from?} {--queue}';

    protected $description = 'Retrieve all (modified) stock';

    public function handle(): int
    {
        /** @var ?string $from */
        $from = $this->argument('from');

        /** @var bool $defer */
        $defer = ! $this->option('queue');

        $date = $from !== null
            ? Carbon::parse($from)
            : null;

        RetrieveAllStockJob::dispatch($date, $defer);

        return static::SUCCESS;
    }
}
