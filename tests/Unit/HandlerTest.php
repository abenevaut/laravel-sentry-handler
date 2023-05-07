<?php

namespace Tests\Unit;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Psr\Log\LoggerInterface;
use Sentry\Laravel\Facade as SentryFacade;
use Sentry\State\HubInterface;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    protected $app;

    /**
     * @runInSeparateProcess
     */
    public function testToReportStandardException()
    {
        // test case exception
        $exception = new \Exception('report to sentry');

        $mock = $this->app->make(LoggerInterface::class);
        $mock->shouldReceive('error')->with($exception->getMessage(), \Mockery::any());

        $mock = $this->app->make(ExceptionHandler::class);
        $mock->shouldReceive('shouldReport')->with($exception)->andReturnTrue()->once();

        $mock = $this->app->make(HubInterface::class);
        $mock->shouldReceive('captureException')->with($exception)->once();
        $mock->shouldNotReceive('captureMessage');

        // test
        $this->app->make(ExceptionHandler::class)->report($exception);
    }

    /**
     * @runInSeparateProcess
     */
    public function testToReportScopedException()
    {
        $exception = \Mockery::mock('\abenevaut\SentryHandler\Contracts\ExceptionAbstract[isSentryMessage,getMessage]');
        $exception->shouldReceive('isSentryMessage')->andReturn(false)->atLeast(2);

        $mock = $this->app->make(ExceptionHandler::class);
        $mock->shouldReceive('shouldReport')->with($exception)->andReturnTrue()->once();

        $mock = $this->app->make(HubInterface::class);
        $mock->shouldReceive('captureException')->with($exception)->once();
        $mock->shouldNotReceive('captureMessage');

        // report
        $this->app->make(ExceptionHandler::class)->report($exception);

        $this->assertFalse($exception->isSentryMessage());
    }

    /**
     * @runInSeparateProcess
     */
    public function testToReportScopedExceptionAsSentryMessage()
    {
        $exception = \Mockery::mock('\abenevaut\SentryHandler\Contracts\ExceptionAbstract[isSentryMessage,getMessage]');
        $exception->shouldReceive('isSentryMessage')->andReturn(true)->atLeast(2);
        // @todo: find a way to mock getMessage on exception
        //        $exception->shouldReceive('getMessage')->andReturn('report to sentry')->once();

        $mock = $this->app->make(ExceptionHandler::class);
        $mock->shouldReceive('shouldReport')->with($exception)->andReturnTrue()->once();

        $mock = $this->app->make(HubInterface::class);
        $mock->shouldNotReceive('captureException');
        $mock->shouldReceive('captureMessage')
            // @todo: find a way to mock getMessage on exception
            //            ->with($exception->getMessage(), $exception->getSeverity())
        ;

        // report
        $this->app->make(ExceptionHandler::class)->report($exception);

        $this->assertTrue($exception->isSentryMessage());
    }

    protected function setUp(): void
    {
        $this->app = new Application();

        /*
         * Mock ExceptionHandler
         */
        $mock = \Mockery::mock('\abenevaut\SentryHandler\Handler[isSentryBounded,shouldReport]', [$this->app]);
        $mock->shouldReceive('isSentryBounded')->andReturnTrue()->atLeast(1);

        $this->app->instance(ExceptionHandler::class, $mock);

        /*
         * Mock HubInterface to use with Sentry Facade
         */
        $this->app->instance(HubInterface::class, \Mockery::mock('Sentry\State\HubInterface[captureException,captureMessage]'));
        AliasLoader::getInstance()->alias(HubInterface::class, SentryFacade::class);

        /*
         * Mock LoggerInterface
         */
        $this->app->instance(LoggerInterface::class, \Mockery::mock(LoggerInterface::class));

        /*
         * Mock config service
         */
        $mock = \Mockery::mock(\Illuminate\Config\Repository::class);
        // Refer to call in abenevaut\SentryHandler\Scopes\DefaultScope
        $mock->shouldReceive('get')->with('app.locale')->andReturn('en');

        $this->app->instance('config', $mock);

        /*
         * Mock request service
         */
        $mock = \Mockery::mock(\Illuminate\Http\Request::class);
        // Refer to call in abenevaut\SentryHandler\Scopes\DefaultScope
        $mock->shouldReceive('server')->with('HTTP_X_FORWARDED_FOR')->andReturn('127.0.0.1');
        $mock->shouldReceive('server')->with('HTTP_CF_CONNECTING_IP')->andReturn(null);

        $this->app->instance('request', $mock);

        Facade::setFacadeApplication($this->app);
    }
}
