<?php

return [
    'repository' => \JustBetter\MagentoStock\Repositories\Repository::class,

    /* Queue for the retrieve / update jobs */
    'queue' => 'default',

    /* Queue for the stock comparison jobs */
    'compare_queue' => 'default',

    /* Send stock updates using Magento 2's async endpoints, a configured message queue in Magento is required for this */
    'async' => false,
];
