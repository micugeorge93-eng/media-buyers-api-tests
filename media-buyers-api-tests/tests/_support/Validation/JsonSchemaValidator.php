<?php

declare(strict_types=1);

namespace Tests\Support\Validation;

use JsonSchema\Validator;

final class JsonSchemaValidator
{
    /**
     * @return string[] Validation error messages; empty when valid.
     */
    public static function validate(string $json, string $schemaPath): array
    {
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['Response body is not valid JSON: ' . json_last_error_msg()];
        }

        if (!is_file($schemaPath)) {
            return ['Schema file not found: ' . $schemaPath];
        }

        $schema = json_decode((string) file_get_contents($schemaPath));
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['Schema file is not valid JSON: ' . json_last_error_msg()];
        }

        $validator = new Validator();
        $validator->validate($data, $schema);

        if ($validator->isValid()) {
            return [];
        }

        return array_map(
            static fn(array $error): string => sprintf(
                '[%s] %s',
                implode('.', $error['property'] !== '' ? explode('.', $error['property']) : ['root']),
                $error['message']
            ),
            $validator->getErrors()
        );
    }
}
