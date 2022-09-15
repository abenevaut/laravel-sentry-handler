<?php

namespace abenevaut\SentryHandler\Traits;

use abenevaut\SentryHandler\Contracts\ExceptionAbstract;
use Sentry\Laravel\Facade as SentryFacade;
use Throwable;

/**
 * Should be used in `Illuminate\Foundation\Exceptions\Handler` context.
 */
trait SentryHandlerTrait
{
    /**
     * @return bool
     */
    public function isSentryBounded(): bool
    {
        return $this->container->bound('sentry') === true;
    }

    /**
     * Allows to report to Sentry all standard exceptions throwed.
     *
     * @param  Throwable  $exception
     */
    public function reportSentry(Throwable $exception): void
    {
        $isExceptionScoped = $exception instanceof ExceptionAbstract;

        if (
            $this->isSentryBounded()
            && $this->shouldReport($exception) === true
            && $isExceptionScoped === false
        ) {
            SentryFacade::captureException($exception);
        }
    }
}
