<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Example;
use Tests\Support\ApiTester;
use Tests\Support\Client\MediaBuyersApiClient;

/**
 * GET /api/mediabuyers — acceptance criteria G1–G7.
 */
final class GetMediaBuyersCest
{
    private MediaBuyersApiClient $client;

    public function _before(ApiTester $I): void
    {
        $this->client = new MediaBuyersApiClient($I);
    }

    /**
     * G1, G2, G4 — successful list response shape and schema conformance.
     */
    public function listReturns200WithJsonContentTypeAndValidSchema(ApiTester $I): void
    {
        $this->client->listMediaBuyers();

        $I->seeResponseCodeIs(200);
        $I->seeJsonContentType();
        $I->validateResponseMatchesSchema('get-media-buyers-schema.json');
    }

    /**
     * G3 — empty collection is represented as an array, not null or 404.
     */
    public function listDataIsAlwaysAnArray(ApiTester $I): void
    {
        $this->client->listMediaBuyers();

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data');

        $data = $I->grabDataFromResponseByJsonPath('$.data')[0];
        $I->assertIsArray($data, 'The data field must always be an array, including when empty.');
    }

    /**
     * G5 — every returned email is syntactically valid.
     */
    public function listItemsHaveValidEmailAddresses(ApiTester $I): void
    {
        $this->client->listMediaBuyers();

        $I->seeResponseCodeIs(200);
        $items = $I->grabDataFromResponseByJsonPath('$.data')[0];
        $I->assertAllEmailsAreValid($items);
    }

    /**
     * G6, G7 — active flag domain and id uniqueness across the collection.
     */
    public function listItemsHaveValidActiveValuesAndUniqueIds(ApiTester $I): void
    {
        $this->client->listMediaBuyers();

        $I->seeResponseCodeIs(200);
        $items = $I->grabDataFromResponseByJsonPath('$.data')[0];

        $I->assertAllActiveValuesAreZeroOrOne($items);
        $I->assertAllIdsAreUnique($items);
    }
}
