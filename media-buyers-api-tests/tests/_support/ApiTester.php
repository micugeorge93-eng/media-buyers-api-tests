<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * @method void sendGET(string $url, array $params = [])
 * @method void sendPOST(string $url, array $params = [], array $files = [])
 * @method void seeResponseCodeIs(int $code)
 * @method void seeResponseIsJson()
 * @method void seeResponseContainsJson(array $json)
 * @method void seeResponseJsonMatchesJsonPath(string $path)
 * @method array grabDataFromResponseByJsonPath(string $path)
 * @method void assertSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertIsArray(mixed $actual, string $message = '')
 * @method void assertIsInt(mixed $actual, string $message = '')
 * @method void assertGreaterThan(mixed $expected, mixed $actual, string $message = '')
 * @method void assertArrayNotHasKey(int|string $key, array $array, string $message = '')
 * @method void assertStringContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertNotEmpty(mixed $actual, string $message = '')
 * @method void setJsonHeaders()
 * @method void validateResponseMatchesSchema(string $schemaFilename)
 * @method void seeJsonContentType()
 * @method void assertErrorsContainDetail(string $expectedDetail)
 * @method void assertAllEmailsAreValid(array $items)
 * @method void assertAllActiveValuesAreZeroOrOne(array $items)
 * @method void assertAllIdsAreUnique(array $items)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;
}
