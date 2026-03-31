<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Commands\Retrieval;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class RetrieveAllStockCommandTest extends TestCase
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

        Bus::assertDispatched(RetrieveAllStockJob::class, fn (RetrieveAllStockJob $job): bool => $job->from instanceof Carbon);
    }
}
