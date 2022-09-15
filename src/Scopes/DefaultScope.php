<?php

namespace abenevaut\SentryHandler\Scopes;

use abenevaut\SentryHandler\Contracts\ScopeAbstract;
use Closure;
use Sentry\State\Scope;

final class DefaultScope extends ScopeAbstract
{
    public function handle(Scope $scope, Closure $next)
    {
        $scope
            ->setUser([
                'ip_address' => $this->determinateRequestIP(),
            ])
            ->setTags([
                'locale' => app()->getLocale(),
            ]);

        return $next($scope);
    }

    /**
     * Looks into HTTP_X_FORWARDED_FOR & HTTP_CF_CONNECTING_IP headers
     * to determinate IP where come from the request.
     *
     * @return string a valid IP
     */
    protected function determinateRequestIP(): string
    {
        $ip = request()->server('HTTP_X_FORWARDED_FOR') ?? request()->ip();
        $ip = request()->server('HTTP_CF_CONNECTING_IP') ?? $ip;
        $ip = explode(',', $ip);

        return $ip[0] ?? '0.0.0.0';
    }
}
