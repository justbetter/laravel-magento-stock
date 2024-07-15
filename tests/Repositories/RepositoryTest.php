<?php

namespace JustBetter\MagentoStock\Tests\Repositories;

use JustBetter\MagentoStock\Repositories\BaseRepository;
use JustBetter\MagentoStock\Repositories\Repository;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RepositoryTest extends TestCase
{
    #[Test]
    public function getters(): void
    {
        /** @var Repository $repository */
        $repository = app(Repository::class);

        $this->assertEquals('Repository', $repository->name());
        $this->assertEquals(250, $repository->retrieveLimit());
        $this->assertEquals(250, $repository->updateLimit());
        $this->assertEquals(3, $repository->failLimit());
        $this->assertFalse($repository->msi());
    }

    #[Test]
    public function it_resolves_repository(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);

        $resolved = BaseRepository::resolve();

        $this->assertInstanceOf(FakeRepository::class, $resolved);
    }
}
