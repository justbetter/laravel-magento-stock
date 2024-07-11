<?php

namespace JustBetter\MagentoStock\Actions\Comparison;

use Illuminate\Support\Collection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesMsiStock;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesSimpleStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;

class CompareMsiStock implements ComparesMsiStock
{
    public function __construct(
        protected Magento $magento,
        protected ChecksMagentoExistence $checksMagentoExistence
    ) {
    }

    public function compare(Stock $stock): void
    {
        if (! $this->checksMagentoExistence->exists($stock->sku)) {
            return;
        }

        $search = SearchCriteria::make()
            ->where('sku', '=', $stock->sku)
            ->get();

        $msiStock = $this->magento
            ->lazy('inventory/source-items', $search)
            ->collect();

        if ($this->quantityEquals($stock, $msiStock)) {
            return;
        }

        event(new DifferenceDetectedEvent($stock));

        $stock->update = true;
        $stock->save();
    }

    protected function quantityEquals(Stock $localStock, Collection $msiStock): bool
    {
        $localMsiStock = collect($localStock->msi_stock);

        foreach ($msiStock as $stock) {
            $localQuantity = $localMsiStock[$stock['source_code']] ?? null;

            if ($localQuantity === null) {
                continue;
            }

            if ($stock['quantity'] !== $localQuantity) {
                activity()
                    ->on($localStock)
                    ->log("Detected MSI quantity difference {$stock['source_code']} Magento: {$stock['quantity']} - local: $localQuantity");

                return false;
            }
        }

        return true;
    }

    public static function bind(): void
    {
        app()->singleton(ComparesSimpleStock::class, static::class);
    }
}
