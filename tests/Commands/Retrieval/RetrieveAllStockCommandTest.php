<?php

namespace JustBetter\MagentoStock\Tests\Commands\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveAllStockCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(RetrieveAllStockCommand::class);

        Bus::assertDispatched(RetrieveAllStockJob::class);
    }
        #[Test]
    public function it_dispatches_job_with_date(): void
    {
        Bus::fake();

        $this->artisan(RetrieveAllStockCommand::class, ['from' => 'now']);

        Bus::assertDispatched(RetrieveAllStockJob::class, function(RetrieveAllStockJob $job): bool {
            return $job->from !== null;
        });
    }
}
