<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Contracts\Retrieval;

interface RetrievesStock
{
    public function retrieve(string $sku, bool $forceUpdate): void;
}
