<?php

namespace JustBetter\MagentoStock\Actions\Retrieval;

use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesAllStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class RetrieveAllStock implements RetrievesAllStock
{
    public function retrieve(?Carbon $from): void
    {
        $repository = BaseRepository::resolve();

        $repository->skus($from)->each(fn(string $sku) => RetrieveStockJob::dispatch($sku));
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesAllStock::class, static::class);
    }
}
