<?php

declare(strict_types=1);

/**
 * Parameterized cases for P4 — active boolean mapping.
 *
 * @return array<string, array{active: bool, expectedActive: int}>
 */
return [
    'active true maps to 1' => [
        'active' => true,
        'expectedActive' => 1,
    ],
    'active false maps to 0' => [
        'active' => false,
        'expectedActive' => 0,
    ],
];
