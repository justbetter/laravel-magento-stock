<?php

namespace JustBetter\MagentoStock\Tests\Commands\Update;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Update\UpdateStockCommand;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateStockCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        Stock::query()->create([
            'sku' => '::sku::',
            'update' => false,
        ]);

        $this->artisan(UpdateStockCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(UpdateStockJob::class);
    }
}
