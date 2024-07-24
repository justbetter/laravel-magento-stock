<?php

namespace JustBetter\MagentoStock\Contracts\Retrieval;

interface RetrievesStock
{
    public function retrieve(string $sku, bool $forceUpdate): void;
}
