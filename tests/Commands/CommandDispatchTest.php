<?php

namespace JustBetter\MagentoStock\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Commands\Comparison\CompareStockCommand;
use JustBetter\MagentoStock\Commands\MonitorWaitTimesCommand;
use JustBetter\MagentoStock\Commands\ProcessStocksCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveStockCommand;
use JustBetter\MagentoStock\Commands\RetrieveUpdatedStockCommand;
use JustBetter\MagentoStock\Commands\Update\UpdateStockCommand;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;
use JustBetter\MagentoStock\Jobs\MonitorWaitTimesJob;
use JustBetter\MagentoStock\Jobs\ProcessStocksJob;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\RetrieveUpdatedStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Tests\TestCase;

class CommandDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    /** @dataProvider dataProvider */
    public function test_simple_commands(string $command, string $job, array $params = []): void
    {
        $this->artisan($command, $params);

        Bus::assertDispatched($job);
    }

    public static function dataProvider(): array
    {
        return [
            'Retrieve' => [
                'command' => RetrieveStockCommand::class,
                'job' => RetrieveStockJob::class,
                'args' => ['sku' => '::sku::'],
            ],
            'Retrieve Updated' => [
                'command' => RetrieveUpdatedStockCommand::class,
                'job' => RetrieveUpdatedStockJob::class,
            ],
            'Retrieve Updated by date' => [
                'command' => RetrieveUpdatedStockCommand::class,
                'job' => RetrieveUpdatedStockJob::class,
                'args' => ['from' => 'yesterday'],
            ],
            'Retrieve All' => [
                'command' => RetrieveAllStockCommand::class,
                'job' => RetrieveAllStockJob::class,
            ],
            'Update' => [
                'command' => UpdateStockCommand::class,
                'job' => UpdateStockJob::class,
                'args' => ['sku' => '::sku::'],
            ],
            'Compare' => [
                'command' => CompareStockCommand::class,
                'job' => DispatchComparisonsJob::class,
            ],
            'Sync' => [
                'command' => ProcessStocksCommand::class,
                'job' => ProcessStocksJob::class,
            ],
            'Monitor Wait' => [
                'command' => MonitorWaitTimesCommand::class,
                'job' => MonitorWaitTimesJob::class,
            ],
        ];
    }
}
