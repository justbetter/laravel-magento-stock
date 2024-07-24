<?php

namespace JustBetter\MagentoStock\Tests\Commands\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveStockCommand;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveStockCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(RetrieveStockCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(RetrieveStockJob::class);
    }
}
