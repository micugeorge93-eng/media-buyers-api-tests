<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;
use Codeception\Module\REST;
use JsonSchema\Validator;
use Tests\Support\Validation\JsonSchemaValidator;

class Api extends Module
{
    public function setJsonHeaders(): void
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        $rest->haveHttpHeader('Content-Type', 'application/json');
        $rest->haveHttpHeader('Accept', 'application/json');
    }

    public function seeJsonContentType(): void
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        $rest->seeHttpHeader('Content-Type', 'application/json');
    }

    public function validateResponseMatchesSchema(string $schemaFilename): void
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        $responseBody = $rest->grabResponse();

        $schemaPath = codecept_root_dir() . 'tests/schemas/' . $schemaFilename;
        $errors = JsonSchemaValidator::validate($responseBody, $schemaPath);

        $this->assertEmpty(
            $errors,
            'Response does not match schema ' . $schemaFilename . ":\n" . implode("\n", $errors)
        );
    }

    public function assertErrorsContainDetail(string $expectedDetail): void
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        $payload = json_decode($rest->grabResponse(), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertIsArray($payload['errors']);

        $details = array_column($payload['errors'], 'detail');
        $this->assertContains(
            $expectedDetail,
            $details,
            'Expected error detail not found. Actual errors: ' . json_encode($payload['errors'])
        );
    }

    public function assertAllEmailsAreValid(array $items): void
    {
        foreach ($items as $index => $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('email', $item);
            $this->assertMatchesRegularExpression(
                '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                (string) $item['email'],
                sprintf('Item at index %d has an invalid email address.', $index)
            );
        }
    }

    public function assertAllActiveValuesAreZeroOrOne(array $items): void
    {
        foreach ($items as $index => $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('active', $item);
            $this->assertIsInt($item['active'], sprintf('Item at index %d active is not an integer.', $index));
            $this->assertContains(
                $item['active'],
                [0, 1],
                sprintf('Item at index %d active must be 0 or 1.', $index)
            );
        }
    }

    public function assertAllIdsAreUnique(array $items): void
    {
        $ids = array_column($items, 'id');
        $this->assertSame(count($ids), count(array_unique($ids)), 'Duplicate id values found in response.');
    }
}
