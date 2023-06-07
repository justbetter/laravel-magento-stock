<?php

use JustBetter\MagentoStock\Calculator\SimpleStockCalculator;
use JustBetter\MagentoStock\Retriever\DummySkuRetriever;
use JustBetter\MagentoStock\Retriever\DummyStockRetriever;

return [
    'retriever' => [
        /* Class that is responsible for retrieving stock */
        'stock' => DummyStockRetriever::class,

        /* Class that is responsible for retrieving sku's */
        'sku' => DummySkuRetriever::class,
    ],

    /* Class to calculate stock */
    'calculator' => SimpleStockCalculator::class,

    /* Set to true if Magento MSI is enabled */
    'msi' => false,

    /* Set to true if this package also needs to update the backorder status */
    'backorders' => false,

    /* Queue for the retrieve / update jobs */
    'queue' => 'default',

    /* Queue for the stock comparison jobs */
    'compare_queue' => 'default',

    /* How many stocks may be retrieved from the source per sync */
    'retrieve_limit' => 100,

    /* How many stocks may be updated to Magento per sync */
    'update_limit' => 100,

    'fails' => [
        /* How many times may an update fail before stopping the syncing */
        'count' => 3,
    ],

    'monitor' => [
        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'retrieval_max_wait' => 30,

        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'update_max_wait' => 30,
    ],

    /* Send stock updates using Magento 2's async endpoints, a configured message queue in Magento is required for this */
    'async' => false,
];
