# Laravel Magento Stock

This packages facilitates a way to push stock to Magento from a configurable source.
Both simple stock and MSI are supported.

## Features

This package provides all the logic of synchronizing stock to Magento so that you only have to write the way stock is
retrieved.

Features:

- Retrieve stock from any source using your own retriever class
- MSI support
- Only update stock when there are modifications
- Automatically stop syncing when there are too many errors (configurable)
- Compare stock between Magento and this package
- Logs activities using [Spatie activitylog](https://github.com/spatie/laravel-activitylog)
- Logs errors using [JustBetter Error Logger](https://github.com/justbetter/laravel-error-logger)
- Checks if Magento products exist
  using [JustBetter Magento Products](https://github.com/justbetter/laravel-magento-products)

## Installation

Require this package: `composer require justbetter/laravel-magento-stock`

Publish the config

```
php artisan vendor:publish --provider="JustBetter\MagentoStock\ServiceProvider" --tag="config"
```

Publish the activity log's migrations:

```
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
```

Run migrations.

```
php artisan migrate
```

> **_TIP:_** All actions in this package are run via jobs, we recommend Laravel Horion or another queueing system to run these


## Setup

In order to sync stock you have to write a retriever class.
This class is responsible for retrieving stock from your source for a single sku.

For example:

```php
<?php

namespace App\Integrations\Stock;

class SomeStockRetriever implements \JustBetter\MagentoStock\Contracts\RetrievesStock
{
    public __construct(protected ExternalStockSource $source) []

    public function retrieve(string $sku): ?StockData
    {
        $quantity = $this->source->retrieve($sku);

        return StockData::make($sku, $quantity);
    }
}
```

You also have to implement a class that retrieves skus.

For example:

```php
<?php

namespace App\Integrations\Stock;

class SomeStockSkuRetriever implements \JustBetter\MagentoStock\Contracts\RetrievesStock
{
    public __construct(protected ExternalProductSource $source) []

    public function retrieveAll(): Enumerable
    {
        return $this->source->all()->pluck('sku');
    }

     public function retrieveUpdated(?Carbon $from = null): Enumerable
    {
        return $this->source->updated($from ?? now()->subDay())->pluck('sku');
    }
}
```

These should return a simple collection of skus:
```['sku_1', 'sku_2', ...]```

You can then register your retrievers in the config file:

```php
<?php

return [
    'retriever' => [
        /* Class that is responsible for retrieving stock */
        'stock' => \App\Integrations\Stock\SomeStockRetriever::class,

        /* Class that is responsible for retrieving sku's */
        'sku' => \App\Integrations\Stock\SomeStockSkuRetriever::class,
    ],
];

```

### Testing your retrievers

To test your retrievers you can use the following commands:

```
php artisan magento:stock:retrieve {sku}
php artisan magento:stock:retrieve-all
php artisan magento:stock:retrieve-updated {from?}
```

### Magento MSI

If you have Magento MSI enabled you have to return the quantity of each source in our retriever.

> **_NOTE:_**  Be sure to set the `msi` config setting to true!

For example:

```php
<?php

namespace App\Integrations\Stock;

class SomeStockRetriever implements \JustBetter\MagentoStock\Contracts\RetrievesStock
{
    public function retrieve(string $sku): ?StockData
    {
        $data = StockData::make($sku);

        // You can set the quantity/status per source
        $data->setMsiQuantity('A', 10);
        $data->setMsiQuantity('B', 0); // Will also set the status to out of stock
        $data->setMsiQuantity('C', 0);

        $data->setMsiStatus('C', true);

        // Or you can set it in bulk
        $data->setMsiQuantities([
            'A' => 10,
            'B' => 0,
            'C' => 0,
        ]);

        $data->setMsiStatusses([
            'A' => true,
            'B' => false,
            'C' => true,
        ]);

        return $data;
    }
}
```


### Custom retrievers

If you cannot retrieve the sku's and stock data separately you can setup a custom retriever.
For example if you have XML or CSV files with stock data that include the sku and a quantity.

The basic flow of your retriever should be:
1. Read stock data your source per sku
2. Build a `\JustBetter\MagentoStock\Data\StockData` object
3. Dispatch a `\JustBetter\MagentoStock\Jobs\ProcessStockJob` job

You can then never run the `magento:stock:retrieve*` commands and schedule your own retriever.
To be extra safe you can still register your own retrieve and return `null` and empty collections to be sure that the default dummy sku/stock retrievers never execute.

## Processing complex stock logic

If you need to apply complex stock logic you can implement a calculator.
A calculator's goal is to modify the `\JustBetter\MagentoStock\Data\StockData` object.
By default this only sets the in/out of stock status based on the quantity.

See the `\JustBetter\MagentoStock\Calculators\SimpleStockCalculator` for an example.

If you want to use your own calculator you can set it in the config file:
```php
<?php

return [
    /* Class to calculate stock */
    'calculator' => SimpleStockCalculator::class,
];

```

## Schedule

```php
<?php

    protected function schedule(Schedule $schedule): void
    {
        // Run every minute to dispatch retrieve & update jobs
        $schedule->command(\JustBetter\MagentoStock\Commands\SyncStockCommand::class)->everyMinute();

        $schedule->command(\JustBetter\MagentoStock\Commands\RetrieveAllStockCommand::class)->dailyAt('05:00');
        $schedule->command(\JustBetter\MagentoStock\Commands\RetrieveUpdatedStockCommand::class)->everyFiveMinutes();

        // Compare all stocks in Magento
        $schedule->command(\JustBetter\MagentoStock\Commands\CompareStockCommand::class)->dailyAt('08:00');
    }
```

## Comparisons

This package provides a way to compare stock quantities in Magento with those in the Laravel database.
If a difference is detected it will start an update for that product.

> **_NOTE:_**  Comparisons for MSI is not supported yet!

## Handling failures

When an update fails it will try again. A fail counter is stored with the model which is increased at each failure.

In the config you can specify how many times the update may be attempted:
```php
<?php

return [
    /* How many times can a price update failed before being cancelled */
    'fail_count' => 5,
];
```

You can restart the updates for a product by setting the `sync` field in the DB to true.

## Long Waits

The sync limits the amount of products that are retrieved/updated each sync.
This may result in long waits if not properly configured for the amount of updates you get.

To detect this you can add the `\JustBetter\MagentoStock\Commands\MonitorWaitTimesCommand` to your schedule.
This will fire the `\JustBetter\MagentoStock\Events\LongWaitDetectedEvent` event in which you can for example trigger more updates or send a notification.

You can configure the limits of when the event will be fired in the config:
```php
<?php

return [
    'monitor' => [
        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'retrieval_max_wait' => 30,

        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'update_max_wait' => 30,
    ]
];
```

## Quality

To ensure the quality of this package, run the following command:

```shell
composer quality
```

This will execute three tasks:

1. Makes sure all tests are passed
2. Checks for any issues using static code analysis
3. Checks if the code is correctly formatted

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vincent Boon](https://github.com/VincentBean)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

