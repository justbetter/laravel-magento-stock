<?php

namespace JustBetter\MagentoStock\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveAllStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveAllStockTest extends TestCase
{
    #[Test]
    public function it_dispatches_jobs(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);
        Bus::fake();

        /** @var RetrieveAllStock $action */
        $action = app(RetrieveAllStock::class);
        $action->retrieve(null);

        Bus::assertDispatched(RetrieveStockJob::class);
    }
}
