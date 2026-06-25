<?php

declare(strict_types=1);

namespace Tests\Support\Client;

use Tests\Support\ApiTester;

final class MediaBuyersApiClient
{
    private const ENDPOINT = '/api/mediabuyers';

    public function __construct(private readonly ApiTester $tester)
    {
    }

    public function listMediaBuyers(): void
    {
        $this->tester->setJsonHeaders();
        $this->tester->sendGET(self::ENDPOINT);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createMediaBuyer(array $payload): void
    {
        $this->tester->setJsonHeaders();
        $this->tester->sendPOST(self::ENDPOINT, $payload);
    }
}
