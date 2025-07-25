<?php

namespace JustBetter\MagentoStock\Actions\Retrieval;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesAllStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class RetrieveAllStock implements RetrievesAllStock
{
    public function retrieve(?Carbon $from, bool $defer = true): void
    {
        $repository = BaseRepository::resolve();

        if (! $defer) {
            $repository->skus($from)->each(fn (string $sku) => RetrieveStockJob::dispatch($sku));

            return;
        }

        $date = now();

        $repository->skus($from)->chunk(250)->each(function (Enumerable $skus) use ($date): void {
            $existing = Stock::query()
                ->whereIn('sku', $skus)
                ->pluck('sku');

            Stock::query()
                ->whereIn('sku', $existing)
                ->update(['retrieve' => true]);

            Stock::query()->insert(
                $skus
                    ->diff($existing)
                    ->values()
                    ->map(fn (string $sku): array => [
                        'sku' => $sku,
                        'retrieve' => true,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ])->toArray()
            );
        });
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesAllStock::class, static::class);
    }
}
