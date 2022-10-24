<?php

namespace JustBetter\MagentoStock\Contracts;

interface ComparesStock
{
    public function compare(string $sku): void;
}
