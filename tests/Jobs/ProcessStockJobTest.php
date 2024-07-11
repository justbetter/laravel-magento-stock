<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Exception;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoStock\Contracts\Retrieval\SavesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Jobs\ProcessStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Retriever\DummyStockRetriever;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class ProcessStockJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(SavesStock::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->once();
        });

        /** @var DummyStockRetriever $retriever */
        $retriever = app(DummyStockRetriever::class);

        /** @var StockData $stock */
        $stock = $retriever->retrieve('::sku::');

        ProcessStockJob::dispatch($stock);
    }

    public function test_unique_id_and_tags(): void
    {
        $job = new ProcessStockJob(StockData::make('::sku::'));

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }

    public function test_failed(): void
    {
        $model = Stock::query()->create(['sku' => '::sku::']);

        $job = new ProcessStockJob(StockData::make('::sku::'));

        $job->failed(new Exception('Testing'));

        /** @var Error $error */
        $error = $model->errors()->first();

        $this->assertNotNull($error);
        $this->assertTrue(str_contains($error->message ?? '', '::sku::'));
    }
}
