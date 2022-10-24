<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Exception;
use Illuminate\Support\Facades\Bus;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoStock\Jobs\ProcessStockJob;
use JustBetter\MagentoStock\Jobs\RetrieveStockJob;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class RetrieveStockJobTest extends TestCase
{
    public function test_it_dispatches_process_job(): void
    {
        Bus::fake([ProcessStockJob::class]);

        RetrieveStockJob::dispatch('::sku::');

        Bus::assertDispatched(ProcessStockJob::class, function (ProcessStockJob $job) {
            return $job->stock->sku === '::sku::';
        });
    }

    public function test_unique_id_and_tags(): void
    {
        $job = new RetrieveStockJob('::sku::');

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }

    public function test_failed(): void
    {
        $model = MagentoStock::query()->create(['sku' => '::sku::']);

        $job = new RetrieveStockJob('::sku::');

        $job->failed(new Exception('Testing'));

        /** @var Error $error */
        $error = $model->errors()->first();

        $this->assertNotNull($error);
        $this->assertTrue(str_contains($error->message ?? '', '::sku::'));
    }
}
