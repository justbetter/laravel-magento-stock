<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\UpdatesBackorders;
use JustBetter\MagentoStock\Contracts\UpdatesStock;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Models\MagentoStock;
use Throwable;

class UpdateStockJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public string $sku)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(
        UpdatesStock $stock,
        UpdatesBackorders $backorders,
        ChecksMagentoExistence $checksMagentoExistence,
    ): void {
        /** @var MagentoStock $model */
        $model = MagentoStock::query()
            ->where('sku', '=', $this->sku)
            ->firstOrFail();

        if ($checksMagentoExistence->exists($this->sku)) {
            $stock->update($model);
            $backorders->update($model);
        }

        $model->update(['update' => false]);
    }

    public function uniqueId(): string
    {
        return $this->sku;
    }

    public function tags(): array
    {
        return [
            $this->sku,
        ];
    }

    public function failed(Throwable $exception): void
    {
        $log = Error::log()
            ->withGroup('Stock')
            ->withMessage("Failed while updating for sku $this->sku")
            ->fromThrowable($exception);

        $stockModel = MagentoStock::query()
            ->where('sku', $this->sku)
            ->first();

        if ($stockModel !== null) {
            $log->withModel($stockModel);
        }

        if (is_a($exception, UpdateException::class)) {
            $log->withDetails($exception->payload);
        }

        $log->save();
    }
}
