<?php

namespace JustBetter\MagentoStock\Actions;

use Exception;
use Illuminate\Support\Collection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\ComparesStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\MagentoStock;

class CompareMsiStock implements ComparesStock
{
    public function __construct(
        protected Magento $magento,
        protected ChecksMagentoExistence $checksMagentoExistence
    ) {
    }

    public function compare(string $sku): void
    {
        if (! $this->checksMagentoExistence->exists($sku)) {
            return;
        }

        /** @var MagentoStock $localStock */
        $localStock = MagentoStock::query()
            ->where('sku', '=', $sku)
            ->firstOrFail();

        $search = SearchCriteria::make()
            ->where('sku', '=', $sku)
            ->paginate(1, 100)
            ->get();

        $msiStock = $this->magento
            ->get("inventory/source-items", $search)
            ->throw()
            ->collect('items');

        if ($this->quantityEquals($localStock, $msiStock)) {
            return;
        }

        event(new DifferenceDetectedEvent($localStock));

        $localStock->update = true;
        $localStock->save();
    }

    protected function quantityEquals(MagentoStock $localStock, Collection $msiStock): bool
    {
        $localMsiStock = collect($localStock->msi_stock);

        foreach ($msiStock as $stock) {
            $localQuantity = $localMsiStock[$stock['source_code']] ?? null;

            if ($localQuantity === null) {
                continue;
            }

            if ($stock['quantity'] !== $localQuantity) {

                activity()
                    ->performedOn($localStock)
                    ->log("Detected MSI quantity difference {$stock['source_code']} Magento: {$stock['quantity']} - local: $localQuantity");

                return false;
            }
        }

        return true;
    }

    public static function bind(): void
    {
        app()->singleton(ComparesStock::class, static::class);
    }
}
