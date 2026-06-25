<?php

declare(strict_types=1);

/**
 * Parameterized cases for P5 — missing required fields.
 *
 * @return array<string, array{builderMethod: string, expectedDetail: string}>
 */
return [
    'missing mbId' => [
        'builderMethod' => 'withoutMbId',
        'expectedDetail' => 'This field is missing: [mbId]',
    ],
    'missing name' => [
        'builderMethod' => 'withoutName',
        'expectedDetail' => 'This field is missing: [name]',
    ],
    'missing email' => [
        'builderMethod' => 'withoutEmail',
        'expectedDetail' => 'This field is missing: [email]',
    ],
    'missing active' => [
        'builderMethod' => 'withoutActive',
        'expectedDetail' => 'This field is missing: [active]',
    ],
];
