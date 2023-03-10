<?php

declare(strict_types=1);

use Waglpz\Cli\UI\Cli\DbMigrations;
use Waglpz\Cli\UI\Cli\DbReset;

return [
    DbMigrations::class => [
        'shared'          => true,
        'constructParams' => [
            (include __DIR__ . '/../cli.php')['commands']['db:migrations']['options'],
        ],
    ],
    DbReset::class      => [
        'shared'          => true,
        'constructParams' => [
            (include __DIR__ . '/../cli.php')['commands']['db:migrations']['options'],
        ],
    ],
];
