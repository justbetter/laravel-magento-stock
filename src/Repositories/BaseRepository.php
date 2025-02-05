<?php

namespace JustBetter\MagentoStock\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoStock\Data\StockData;

abstract class BaseRepository
{
    protected string $name = 'Repository';

    protected int $retrieveLimit = 250;

    protected int $updateLimit = 250;

    protected int $failLimit = 3;

    protected bool $msi = false;

    protected bool $backorders = false;

    public function name(): string
    {
        return $this->name;
    }

    public function retrieveLimit(): int
    {
        return $this->retrieveLimit;
    }

    public function updateLimit(): int
    {
        return $this->updateLimit;
    }

    public function failLimit(): int
    {
        return $this->failLimit;
    }

    public function msi(): bool
    {
        return $this->msi;
    }

    public function backorders(): bool
    {
        return $this->backorders;
    }

    public static function resolve(): BaseRepository
    {
        /** @var ?class-string<BaseRepository> $repository */
        $repository = config('magento-stock.repository');

        throw_if($repository === null, 'Repository has not been found.');

        /** @var BaseRepository $instance */
        $instance = app($repository);

        return $instance;
    }

    /** @return Enumerable<int, string> */
    abstract public function skus(?Carbon $from = null): Enumerable;

    abstract public function retrieve(string $sku): ?StockData;
}
