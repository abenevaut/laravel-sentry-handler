<?php

namespace abenevaut\SentryHandler\Contracts;

use Closure;
use Sentry\State\Scope;

abstract class ScopeAbstract
{
    /**
     * @param  Scope  $scope
     * @param  Closure  $next
     * @return mixed
     */
    abstract public function handle(Scope $scope, Closure $next);
}
