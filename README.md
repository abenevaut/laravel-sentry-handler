# Installation

```
composer require abenevaut/laravel-sentry-handler
php artisan sentry:publish --dsn=
```

## Two ways to customize your handler

### Inheritance from `abenevaut\SentryHandler\Handler`

In `app/Exceptions/Handler.php`
Replace `use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;` by `use abenevaut\SentryHandler\Handler as ExceptionHandler;`

Note: that method is used in https://github.com/abenevaut/demo-laravel-sentry-handler

### Use the trait `abenevaut\SentryHandler\Traits\SentryHandlerTrait`

In `app/Exceptions/Handler.php`
Add `use SentryHandlerTrait;` in `App\Exceptions\Handler` class
Then adjust your `report()` method

```
public function report(\Throwable $e): void
{
    // Report standard exceptions to sentry
    $this->reportSentry($e);

    parent::report($e);
}
```