<?php

namespace abenevaut\SentryHandler;

use abenevaut\SentryHandler\Contracts\SentryHandlerInterface;
use abenevaut\SentryHandler\Traits\SentryHandlerTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler implements SentryHandlerInterface
{
    use SentryHandlerTrait;

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Throwable
     */
    public function report(\Throwable $e): void
    {
        // Report standard exceptions to sentry
        $this->reportSentry($e);

        parent::report($e);
    }
}
