<?php

namespace abenevaut\SentryHandler\Contracts;

use abenevaut\SentryHandler\Scopes\DefaultScope;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pipeline\Pipeline;
use Sentry\Laravel\Facade as SentryFacade;
use Sentry\Severity;
use Sentry\State\Scope;

abstract class ExceptionAbstract extends \Exception
{
    /**
     * Define if exception should be.
     *
     * @var bool
     */
    protected bool $isSentryMessage = false;

    /**
     * @var array|string[]
     */
    private array $scopes = [
        DefaultScope::class,
    ];

    /**
     * @param  string|ScopeAbstract  $scope
     * @return $this
     *
     * @throws \Exception
     */
    public function addScope(string|ScopeAbstract $scope): self
    {
        if (is_string($scope) === true && class_exists($scope) === false) {
            throw new \Exception("Class {$scope} does not exist");
        }

        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return bool
     */
    public function isSentryMessage(): bool
    {
        return $this->isSentryMessage;
    }

    /**
     * @return Severity
     */
    public function getSeverity(): Severity
    {
        return Severity::warning();
    }

    /**
     * Dedicated to Illuminate\Foundation\Exceptions\Handle::report
     * The Foundation handler will report to sentry based on the exception report method.
     *
     * @return void
     */
    public function report(): void
    {
        if (app(ExceptionHandler::class)?->isSentryBounded()) {
            \Sentry\configureScope(function (Scope $scope): void {
                app(Pipeline::class)
                    ->send($scope)
                    ->through($this->getScopes())
                    ->thenReturn();
            });

            $this->isSentryMessage()
                ? SentryFacade::captureMessage($this->getMessage(), $this->getSeverity())
                : SentryFacade::captureException($this);
        }
    }
}
