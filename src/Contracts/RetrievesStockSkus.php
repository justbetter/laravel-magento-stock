<?php

namespace JustBetter\MagentoStock\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;

interface RetrievesStockSkus
{
    /** @return Enumerable<int, string> */
    public function retrieveAll(): Enumerable;

    /** @return Enumerable<int, string> */
    public function retrieveUpdated(?Carbon $from = null): Enumerable;
}
