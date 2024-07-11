<?php

namespace JustBetter\MagentoStock\Tests\Provider;

use JustBetter\MagentoStock\Actions\Update\Sync\UpdateMsiStock;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateSimpleStock;
use JustBetter\MagentoStock\Contracts\UpdatesStock;
use JustBetter\MagentoStock\ServiceProvider;
use JustBetter\MagentoStock\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_simple_binding(): void
    {
        config()->set('magento-stock.msi', false);

        $provider = new ServiceProvider(app());
        $provider->boot();

        /** @var UpdatesStock $updater */
        $updater = app(UpdatesStock::class);

        $this->assertTrue(is_a($updater, UpdateSimpleStock::class));
    }

    public function test_msi_binding(): void
    {
        config()->set('magento-stock.msi', true);

        $provider = new ServiceProvider(app());
        $provider->boot();

        /** @var UpdatesStock $updater */
        $updater = app(UpdatesStock::class);

        $this->assertTrue(is_a($updater, UpdateMsiStock::class));
    }
}
