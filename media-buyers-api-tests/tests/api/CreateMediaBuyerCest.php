<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Example;
use Tests\Support\ApiTester;
use Tests\Support\Builders\CreateMediaBuyerRequestBuilder;
use Tests\Support\Client\MediaBuyersApiClient;

/**
 * POST /api/mediabuyers — acceptance criteria P1–P11.
 */
final class CreateMediaBuyerCest
{
    private MediaBuyersApiClient $client;

    public function _before(ApiTester $I): void
    {
        $this->client = new MediaBuyersApiClient($I);
    }

    /**
     * P1 — valid create returns 200, JSON content type, and schema-conformant body.
     */
    public function createValidMediaBuyerReturns200AndMatchesSchema(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(200);
        $I->seeJsonContentType();
        $I->validateResponseMatchesSchema('post-media-buyer-schema.json');
    }

    /**
     * P2, P3 — server-generated id and echoed business fields from the request.
     */
    public function createReturnsServerGeneratedIdAndRequestedBusinessFields(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()
            ->withMbId('9100')
            ->withInitials('AB')
            ->withName('Alice Buyer')
            ->withEmail('alice.buyer@example.com')
            ->withSlackUserId('U0123456789')
            ->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(200);

        $data = $I->grabDataFromResponseByJsonPath('$.data')[0];

        $I->assertArrayNotHasKey('id', $payload, 'The request must never supply id.');
        $I->assertIsInt($data['id']);
        $I->assertGreaterThan(0, $data['id']);

        $I->assertSame($payload['mbId'], $data['mbId']);
        $I->assertSame($payload['initials'], $data['initials']);
        $I->assertSame($payload['name'], $data['name']);
        $I->assertSame($payload['email'], $data['email']);
        $I->assertSame($payload['slackUserId'], $data['slackUserId']);
    }

    /**
     * P4 — active boolean in the request maps to integer 0/1 in the response.
     *
     * @dataProvider activeBooleanMappingProvider
     */
    public function createMapsActiveBooleanToInteger(ApiTester $I, Example $example): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()
            ->withMbId('920' . ($example['active'] ? '1' : '0'))
            ->withActive($example['active'])
            ->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(200);

        $active = $I->grabDataFromResponseByJsonPath('$.data.active')[0];
        $I->assertSame($example['expectedActive'], $active);
    }

    /**
     * P5 — omitting any required field returns 400 and names the missing field.
     *
     * @dataProvider missingRequiredFieldProvider
     */
    public function createRejectsMissingRequiredFields(ApiTester $I, Example $example): void
    {
        $builder = CreateMediaBuyerRequestBuilder::valid();
        $builderMethod = $example['builderMethod'];
        $payload = $builder->$builderMethod()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['errors' => [['detail' => $example['expectedDetail']]]]);
        $I->assertErrorsContainDetail($example['expectedDetail']);
    }

    /**
     * P6 — invalid email format is rejected with a descriptive message.
     */
    public function createRejectsInvalidEmail(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()->withInvalidEmail()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);
        $I->assertErrorsContainDetail('The email not-an-email is not a valid email.');
    }

    /**
     * P7 — initials longer than 2 characters are rejected.
     */
    public function createRejectsInitialsLongerThanTwoCharacters(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()->withInitialsTooLong()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);
        $I->assertErrorsContainDetail('The initials must be exactly 2 characters long.');
    }

    /**
     * P8 — name shorter than 2 or longer than 30 characters is rejected.
     *
     * @dataProvider invalidNameLengthProvider
     */
    public function createRejectsInvalidNameLength(ApiTester $I, Example $example): void
    {
        $builder = CreateMediaBuyerRequestBuilder::valid();
        $builderMethod = $example['builderMethod'];
        $payload = $builder->$builderMethod()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);

        $errors = $I->grabDataFromResponseByJsonPath('$.errors')[0];
        $details = implode(' ', array_column($errors, 'detail'));
        $I->assertStringContainsString('name', strtolower($details));
    }

    /**
     * P9 — mbId must be a non-empty string of digits representing a positive integer.
     */
    public function createRejectsNonNumericMbId(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()->withInvalidMbId()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);

        $errors = $I->grabDataFromResponseByJsonPath('$.errors')[0];
        $I->assertNotEmpty($errors);
        $details = strtolower(implode(' ', array_column($errors, 'detail')));
        $I->assertStringContainsString('mbid', $details);
    }

    /**
     * P10 — active must be a boolean, not a string or other type.
     */
    public function createRejectsNonBooleanActive(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()->withNonBooleanActive()->build();

        $this->client->createMediaBuyer($payload);

        $I->seeResponseCodeIs(400);

        $errors = $I->grabDataFromResponseByJsonPath('$.errors')[0];
        $I->assertNotEmpty($errors);
    }

    /**
     * P11 — duplicate mbId is rejected on the second create attempt.
     *
     * Assumption: the API returns HTTP 409 Conflict for uniqueness violations.
     * See README for rationale when the contract leaves the status code open.
     */
    public function createRejectsDuplicateMbId(ApiTester $I): void
    {
        $payload = CreateMediaBuyerRequestBuilder::valid()
            ->withMbId('9999')
            ->build();

        $this->client->createMediaBuyer($payload);
        $I->seeResponseCodeIs(200);

        $this->client->createMediaBuyer($payload);
        $I->seeResponseCodeIs(409);

        $errors = $I->grabDataFromResponseByJsonPath('$.errors')[0];
        $I->assertNotEmpty($errors);
    }

    /** @return array<string, array<string, mixed>> */
    protected function activeBooleanMappingProvider(): array
    {
        return require codecept_data_dir('active_boolean_mapping.php');
    }

    /** @return array<string, array<string, mixed>> */
    protected function missingRequiredFieldProvider(): array
    {
        return require codecept_data_dir('missing_required_fields.php');
    }

    /** @return array<string, array<string, mixed>> */
    protected function invalidNameLengthProvider(): array
    {
        return require codecept_data_dir('invalid_name_lengths.php');
    }
}
