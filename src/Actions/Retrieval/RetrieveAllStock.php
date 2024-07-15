<?php

namespace JustBetter\MagentoStock\Actions\Retrieval;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesAllStock;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class RetrieveAllStock implements RetrievesAllStock
{
    public function retrieve(?Carbon $from): void
    {
        $repository = BaseRepository::resolve();

        $repository->skus($from)
            ->chunk(1000)
            ->each(
                fn (Collection $chunk): int => Stock::query()
                    ->whereIn('sku', $chunk)
                    ->update([
                        'sync' => true,
                        'update' => true,
                    ])
            );
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesAllStock::class, static::class);
    }
}
