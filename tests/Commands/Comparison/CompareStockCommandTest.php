<?php

namespace JustBetter\MagentoStock\Tests\Commands\Comparison;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Comparison\CompareStockCommand;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CompareStockCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(CompareStockCommand::class);

        Bus::assertDispatched(DispatchComparisonsJob::class);
    }
}
