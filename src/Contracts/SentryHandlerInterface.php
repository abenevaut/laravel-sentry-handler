<?php

namespace abenevaut\SentryHandler\Contracts;

use Throwable;

interface SentryHandlerInterface
{
    /**
     * @return bool
     */
    public function isSentryBounded(): bool;

    /**
     * @param  Throwable  $exception
     */
    public function reportSentry(Throwable $exception): void;
}
