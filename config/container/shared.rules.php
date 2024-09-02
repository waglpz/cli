<?php

declare(strict_types=1);

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Dice\Dice;
use MonologFactory\LoggerFactory;
use Psr\Log\LoggerInterface;

use function Waglpz\Config\config;

return [
    '*'                        => [
        'substitutions' => [
            ExtendedPdoInterface::class      => '$DefaultPDO',
            LoggerInterface::class           => '$DefaultLogger',
        ],
    ],
    '$DefaultPDO'              => [
        'shared'          => true,
        'instanceOf'      => ExtendedPdo::class,
        'constructParams' => [
            /** @phpstan-ignore-next-line */
            config('db')['dsn'],
            /** @phpstan-ignore-next-line */
            config('db')['username'],
            /** @phpstan-ignore-next-line */
            config('db')['password'],
            /** @phpstan-ignore-next-line */
            config('db')['options'] ?? null,
            /** @phpstan-ignore-next-line */
            config('db')['queries'] ?? null,
            /** @phpstan-ignore-next-line */
            config('db')['profiler'] ?? null,
        ],
    ],
    '$DefaultLogger'           => [
        'shared'     => true,
        'instanceOf' => LoggerFactory::class,
        'call'       => [
            [
                'create',
                [
                    $_SERVER['APP_NAME'],
                    /** @phpstan-ignore-next-line */
                    config('logger')['default'],
                ],
                Dice::CHAIN_CALL,
            ],
        ],
    ],
];
