<?php

return [
    'repository' => \JustBetter\MagentoStock\Repositories\Repository::class,

    /* Queue for the retrieve / update jobs */
    'queue' => 'default',

    /* Queue for the stock comparison jobs */
    'compare_queue' => 'default',

    /* Send stock updates using Magento 2's async endpoints, a configured message queue in Magento is required for this */
    'async' => false,

    /* Number of hours before async bulk operations are considered stale and prices can be re-queued */
    'async_stale_hours' => 24,
];
