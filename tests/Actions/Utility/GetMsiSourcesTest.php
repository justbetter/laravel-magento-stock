<?php

namespace JustBetter\MagentoStock\Tests\Actions\Utility;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Actions\Utility\GetMsiSources;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GetMsiSourcesTest extends TestCase
{
    #[Test]
    public function it_gets_msi_sources(): void
    {
        Magento::fake();
        Http::fake([
            'magento/rest/all/V1/inventory/sources?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [
                    [
                        'source_code' => 'A',
                    ],
                    [
                        'source_code' => 'B',
                    ],
                ],
            ]),
        ])->preventStrayRequests();

        /** @var GetMsiSources $action */
        $action = app(GetMsiSources::class);

        $this->assertEquals(['A', 'B'], $action->get());
    }
}
