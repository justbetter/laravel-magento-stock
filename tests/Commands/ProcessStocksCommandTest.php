<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\ProcessStocksCommand;
use JustBetter\MagentoStock\Jobs\ProcessStocksJob;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ProcessStocksCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(ProcessStocksCommand::class);

        Bus::assertDispatched(ProcessStocksJob::class);
    }
}
