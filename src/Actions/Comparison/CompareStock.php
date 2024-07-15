<?php

namespace JustBetter\MagentoStock\Actions\Comparison;

use JustBetter\MagentoStock\Contracts\Comparison\ComparesMsiStock;
use \JustBetter\MagentoStock\Contracts\Comparison\ComparesSimpleStock;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesStock;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class CompareStock implements ComparesStock
{
    public function __construct(
        protected ComparesSimpleStock $simpleStock,
        protected ComparesMsiStock $msiStock
    ) {
    }

    public function compare(Stock $stock): void
    {
        $repository = BaseRepository::resolve();

        if ($repository->msi()) {
            $this->msiStock->compare($stock);
        } else {
            $this->simpleStock->compare($stock);
        }
    }

    public static function bind(): void
    {
        app()->singleton(ComparesStock::class, static::class);
    }
}
