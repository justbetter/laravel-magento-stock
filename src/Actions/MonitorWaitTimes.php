<?php

namespace JustBetter\MagentoStock\Actions;

use JustBetter\MagentoStock\Contracts\MonitorsWaitTimes;
use JustBetter\MagentoStock\Events\LongWaitDetectedEvent;
use JustBetter\MagentoStock\Models\MagentoStock;

class MonitorWaitTimes implements MonitorsWaitTimes
{
    public function monitor(): void
    {
        $this->monitorRetrievals();
        $this->monitorUpdates();
    }

    protected function monitorRetrievals(): void
    {
        $retrievalsPerMinute = config('magento-stock.retrieve_limit');
        $maxWaitTime = config('magento-stock.monitor.retrieval_max_wait');

        $waitingCount = MagentoStock::query()
            ->where('sync', '=', true)
            ->where('retrieve', '=', true)
            ->count();

        $wait = $waitingCount / $retrievalsPerMinute;

        if ($wait > $maxWaitTime) {
            event(new LongWaitDetectedEvent('retrieve', $wait));
        }
    }

    protected function monitorUpdates(): void
    {
        $retrievalsPerMinute = config('magento-stock.update_limit');
        $maxWaitTime = config('magento-stock.monitor.update_max_wait');

        $waitingCount = MagentoStock::query()
            ->where('sync', '=', true)
            ->where('update', '=', true)
            ->count();

        $wait = $waitingCount / $retrievalsPerMinute;

        if ($wait > $maxWaitTime) {
            event(new LongWaitDetectedEvent('update', $wait));
        }
    }

    public static function bind(): void
    {
        app()->singleton(MonitorsWaitTimes::class, static::class);
    }
}
