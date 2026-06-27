# Media Buyers API — Codeception Contract Tests

Automated contract tests for the **Media Buyers** REST resource (`GET` and `POST /api/mediabuyers`), implemented with **PHP** and **Codeception**. There is no live API in this assessment; the suite is written as if a real environment will exist next sprint, with the base URL resolved from configuration.

## Codeception setup

| Piece | Role |
|-------|------|
| **`api` suite** | Holds all REST contract tests (`tests/api/`). |
| **REST module** | Sends HTTP requests and captures responses. Configured with `url: %API_BASE_URL%` so tests never hard-code hosts. |
| **PhpBrowser** | Underlying HTTP client used by REST (assignment-required). |
| **Asserts module** | PHPUnit-style assertions on response payloads and business rules. |
| **Custom `Api` helper** | Shared headers, JSON Schema validation, and reusable contract assertions. |

Configuration entry point: `codeception.yml` loads `.env` (see `.env.example`). The effective base URL is `{{BASE_URL}}/api` as described in the contract — set `API_BASE_URL` to the host root (e.g. `https://staging.example.com`).

To make the suite executable locally (optional, not required for grading):

```bash
composer install
cp .env.example .env
vendor/bin/codecept build
vendor/bin/codecept run api
```

## Repository layout

```
tests/
├── api/                          # Cest classes — one file per endpoint
│   ├── GetMediaBuyersCest.php    # G1–G7
│   └── CreateMediaBuyerCest.php  # P1–P11
├── _support/
│   ├── ApiTester.php             # Actor used by all API tests
│   ├── Helper/Api.php            # Headers, schema validation, shared assertions
│   ├── Client/MediaBuyersApiClient.php   # HTTP boundary — paths & verbs only
│   ├── Builders/CreateMediaBuyerRequestBuilder.php  # Request payload factory
│   └── Validation/JsonSchemaValidator.php
├── _data/                        # Parameterized test data (P4, P5, P8)
└── schemas/                      # Contract JSON Schemas (success responses)
    ├── get-media-buyers-schema.json
    └── post-media-buyer-schema.json
```

**Traceability:** each test method references the acceptance criterion it encodes (e.g. `G2`, `P5`) in its docblock. Schema files live in-repo so a spec change updates `tests/schemas/*.json` first, then assertions that depend on new fields.

## Scenario selection

| Area | Automated | Rationale |
|------|-----------|-----------|
| **GET happy path + schema** (G1, G2, G4) | Yes | Confirms envelope, content type, and structural contract in one place. |
| **Empty list semantics** (G3) | Yes | Easy to miss in manual testing; contract explicitly requires `{"data": []}`. |
| **Email / active / id rules** (G5–G7) | Yes | Schema covers shape; extra tests guard business rules JSON Schema cannot express fully (uniqueness, integer active). |
| **POST create success** (P1–P4) | Yes | Core integration path; validates mapping from request booleans to response integers. |
| **Required-field omissions** (P5) | Yes, parameterized | One test method, four cases — matches contract error format exactly. |
| **Field-level validation** (P6–P10) | Yes | High regression value; each rule is independent and frequently broken. |
| **Uniqueness** (P11) | Yes | Data integrity; two-step flow documents stateful behaviour. |
| *Pagination, auth, PATCH/DELETE, performance* | No | Out of contract scope. |

**Test count:** 4 GET tests + 11 POST test methods (several parameterized)

## Abstractions and what they buy at scale

1. **`MediaBuyersApiClient`** — Single place for paths, verbs, and headers. When the contract adds `/api/v2/mediabuyers`, update one class.
2. **`CreateMediaBuyerRequestBuilder`** — No inline JSON in tests; fluent methods express intent (`withInvalidEmail()`, `withoutName()`). New fields get one builder method, not N test edits.
3. **`JsonSchemaValidator` + schema files** — Success responses validated against the provided artifacts. Schema drift is visible in PR diffs.
4. **`Api` helper** — Cross-cutting assertions (error `detail` matching, email/active/id rules) stay out of Cest methods.
5. **`tests/_data/*.php`** — Parameterized boundaries (missing fields, name lengths, active mapping) stay data-driven for easy extension.

## Future improvements

- **Test data setup/teardown** — Factory + cleanup hook (or dedicated staging seed API) before P11 and list tests that assume existing rows.
- **Parallelization** — Split GET vs POST suites in CI; use Codeception `--shard` for large regressions.
- **CI integration** — GitHub Actions job: `composer install`, `codecept run api`, inject `API_BASE_URL` from secrets.
- **Schema versioning** — Pin schemas under `tests/schemas/v1/` when the API versions; load by env flag.
- **Contract testing** — Publish schemas to a registry; fail CI if implementation OpenAPI diverges from `tests/schemas/`.
- **Mock/stub layer** — WireMock or Prism in CI for deterministic runs before staging exists; swap REST `url` via env only.
- **Reporting** — Allure or JUnit output for pipeline visibility.

## Assumptions (contract silent)

| Topic | Assumption |
|-------|------------|
| **P11 status code** | Duplicate `mbId` returns **409 Conflict** (REST convention for uniqueness). A **400** with an errors array would also be acceptable; only the constant in `createRejectsDuplicateMbId` would change. |
| **P9 error message** | Exact wording for non-numeric `mbId` is unspecified; test asserts **400**, non-empty `errors`, and that a detail mentions `mbId`. |
| **P8 error message** | Wording not fixed in the contract; test asserts **400** and that error details reference `name`. |
| **P10 error message** | Contract requires **400** only; test asserts non-empty `errors` without fixing exact copy. |
| **Optional fields omitted** | When `initials` or `slackUserId` are omitted from POST, behaviour on GET/list is unspecified; builder includes them for happy-path tests only. |
| **GET list content** | G5–G7 run against whatever the environment returns (including empty array); rules apply per item when present. |

## Acceptance criteria coverage

| ID | Test |
|----|------|
| G1 | `GetMediaBuyersCest::listReturns200WithJsonContentTypeAndValidSchema` |
| G2 | same (JSON Schema) |
| G3 | `GetMediaBuyersCest::listDataIsAlwaysAnArray` |
| G4 | same as G2 (schema `required`) |
| G5 | `GetMediaBuyersCest::listItemsHaveValidEmailAddresses` |
| G6–G7 | `GetMediaBuyersCest::listItemsHaveValidActiveValuesAndUniqueIds` |
| P1 | `CreateMediaBuyerCest::createValidMediaBuyerReturns200AndMatchesSchema` |
| P2–P3 | `CreateMediaBuyerCest::createReturnsServerGeneratedIdAndRequestedBusinessFields` |
| P4 | `CreateMediaBuyerCest::createMapsActiveBooleanToInteger` (×2 data sets) |
| P5 | `CreateMediaBuyerCest::createRejectsMissingRequiredFields` (×4 data sets) |
| P6 | `CreateMediaBuyerCest::createRejectsInvalidEmail` |
| P7 | `CreateMediaBuyerCest::createRejectsInitialsLongerThanTwoCharacters` |
| P8 | `CreateMediaBuyerCest::createRejectsInvalidNameLength` (×2 data sets) |
| P9 | `CreateMediaBuyerCest::createRejectsNonNumericMbId` |
| P10 | `CreateMediaBuyerCest::createRejectsNonBooleanActive` |
| P11 | `CreateMediaBuyerCest::createRejectsDuplicateMbId` |
