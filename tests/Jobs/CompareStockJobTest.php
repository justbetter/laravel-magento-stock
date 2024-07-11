<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Exception;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesStock;
use JustBetter\MagentoStock\Jobs\Comparinson\CompareStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class CompareStockJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(ComparesStock::class, function (MockInterface $mock) {
            $mock->shouldReceive('compare')->once();
        });

        CompareStockJob::dispatch('::sku::');
    }

    public function test_it_has_unique_id(): void
    {
        $job = new CompareStockJob('::sku::');
        $this->assertEquals('::sku::', $job->uniqueId());
    }

    public function test_it_logs_error(): void
    {
        $job = new CompareStockJob('::sku::');

        $job->failed(new Exception('::some-error::'));

        $log = Error::query()->first();

        $this->assertTrue(str_contains($log->details ?? '', '::some-error::'));
        $this->assertTrue(str_contains($log->details ?? '', '::sku::'));
    }
}
