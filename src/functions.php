<?php

declare(strict_types=1);

namespace Waglpz\Cli;

if (! \function_exists('Waglpz\Cli\cliExecutorName')) {
    function cliExecutorName(): string
    {
        static $cliExecutor = null;
        if ($cliExecutor === null) {
            $cliExecutor = isset($_SERVER['COMPOSER_HOME'], $_SERVER['COMPOSER_BINARY'])
                ? ' composer waglpz:cli '
                : ' php ' . $_SERVER['argv'][0] . ' ';
        }

        return $cliExecutor;
    }
}
