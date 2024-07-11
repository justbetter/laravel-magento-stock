<?php

namespace JustBetter\MagentoStock\Contracts\Comparinson;

interface ComparesStock
{
    public function compare(string $sku): void;
}
