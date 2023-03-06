<?php

declare(strict_types=1);

namespace Waglpz\Cli;

/** @codeCoverageIgnore */
final class CliExceptionHandler
{
    /** @param array<mixed> $anonymizeLog */
    public function __construct(
        private readonly string|null $logErrorsDir = null,
        private readonly array|null $anonymizeLog = null,
    ) {
    }

    public function __invoke(\Throwable $exception): void
    {
        $code              = $exception->getCode();
        $inputStreamHandle = \fopen('php://input', 'rb');
        \assert(\is_resource($inputStreamHandle));
        \fseek($inputStreamHandle, 0, \SEEK_SET);
        $input = \stream_get_contents($inputStreamHandle);
        \fclose($inputStreamHandle);
        $date       = \date('Y-m-d H:i:s');
        $loggingDir = $this->logErrorsDir ?? '/tmp';

        if ($this->anonymizeLog !== null) {
            $newGlobals = \array_replace_recursive($GLOBALS, $this->anonymizeLog);
            foreach ($GLOBALS as $key => $value) {
                $GLOBALS[$key] = $newGlobals[$key];
            }
        }

        \file_put_contents(
            $loggingDir . '/cli.' . \APP_ENV . '.log',
            $date . ' [ERROR ' . $code . '] ' . $exception->getMessage() . \PHP_EOL
            . $exception->getTraceAsString() . \PHP_EOL
            . $date . ' [SERVER] ' . \preg_replace('#\s+#', ' ', \print_r($_SERVER, true)) . \PHP_EOL
            . $date . ' [INPUT] ' . $input . \PHP_EOL,
            \FILE_APPEND,
        );
    }
}
