<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoStock\Actions\MonitorWaitTimes;
use JustBetter\MagentoStock\Events\LongWaitDetectedEvent;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class MonitorWaitTimesTest extends TestCase
{
    public function test_retrieve_wait_times_dispatches_event(): void
    {
        Event::fake();

        config()->set('magento-stock.retrieve_limit', 10);
        config()->set('magento-stock.monitor.retrieval_max_wait', 5);

        for ($i = 0; $i < 100; $i++) {
            MagentoStock::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => true,
                'update' => false,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertDispatched(LongWaitDetectedEvent::class, function (LongWaitDetectedEvent $event) {
            return $event->type === 'retrieve' && $event->wait === 10;
        });
    }

    public function test_retrieve_wait_times_does_not_dispatch_event(): void
    {
        Event::fake();

        config()->set('magento-stock.retrieve_limit', 10);
        config()->set('magento-stock.monitor.retrieval_max_wait', 10);

        for ($i = 0; $i < 100; $i++) {
            MagentoStock::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => true,
                'update' => false,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertNotDispatched(LongWaitDetectedEvent::class);
    }

    public function test_update_wait_times_dispatches_event(): void
    {
        Event::fake();

        config()->set('magento-stock.update_limit', 10);
        config()->set('magento-stock.monitor.update_max_wait', 5);

        for ($i = 0; $i < 100; $i++) {
            MagentoStock::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => false,
                'update' => true,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertDispatched(LongWaitDetectedEvent::class, function (LongWaitDetectedEvent $event) {
            return $event->type === 'update' && $event->wait === 10;
        });
    }

    public function test_update_wait_times_does_not_dispatch_event(): void
    {
        Event::fake();

        config()->set('magento-stock.update_limit', 10);
        config()->set('magento-stock.monitor.update_max_wait', 10);

        for ($i = 0; $i < 100; $i++) {
            MagentoStock::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => false,
                'update' => true,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertNotDispatched(LongWaitDetectedEvent::class);
    }
}
