<?php

declare(strict_types=1);

/**
 * Parameterized cases for P8 — name length validation.
 *
 * @return array<string, array{builderMethod: string, nameValue: string}>
 */
return [
    'name shorter than 2 characters' => [
        'builderMethod' => 'withNameTooShort',
        'nameValue' => 'A',
    ],
    'name longer than 30 characters' => [
        'builderMethod' => 'withNameTooLong',
        'nameValue' => str_repeat('X', 31),
    ],
];
