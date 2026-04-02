<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Listeners\BulkOperationStatusListener;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class BulkOperationStatusListenerTest extends TestCase
{
    #[Test]
    public function it_handles_complete_status(): void
    {
        Event::fake();

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => 'sku',
            'in_stock' => true,
            'quantity' => 5,
            'last_failed' => now(),
            'fail_count' => 1,
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'method' => 'POST',
            'magento_connection' => 'default',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::Complete,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);
        $listener->execute($operation);

        $model->refresh();

        $this->assertInstanceOf(Carbon::class, $model->last_updated);
        $this->assertNotInstanceOf(Carbon::class, $model->last_failed);
        $this->assertEquals(0, $model->fail_count);

        Event::assertDispatched(StockUpdatedEvent::class);
    }

    #[Test]
    public function it_handles_failed_status(): void
    {
        Event::fake();

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => 'sku',
            'in_stock' => true,
            'quantity' => 5,
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'method' => 'POST',
            'magento_connection' => 'default',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::NotRetriablyFailed,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);
        $listener->execute($operation);

        $model->refresh();

        $this->assertNotInstanceOf(Carbon::class, $model->last_updated);
        $this->assertInstanceOf(Carbon::class, $model->last_failed);
        $this->assertEquals(1, $model->fail_count);
        $this->assertTrue($model->update);

        Event::assertNotDispatched(StockUpdatedEvent::class);
    }
}
