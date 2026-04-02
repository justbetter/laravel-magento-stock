<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Enums;

enum Backorders: int
{
    case NoBackorders = 0;
    case Backorders = 1;
    case BackordersNotify = 2;
}
