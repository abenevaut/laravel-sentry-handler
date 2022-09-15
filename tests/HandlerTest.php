<?php

namespace Tests;

use abenevaut\SentryHandler\Contracts\ExceptionAbstract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Log\LoggerInterface;
use Sentry\Laravel\Facade as SentryFacade;
use Sentry\State\HubInterface;

class HandlerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $mock = \Mockery::mock('\abenevaut\SentryHandler\Handler[isSentryBounded,shouldReport]', [app()]);
        $mock->shouldReceive('isSentryBounded')->andReturn(true)->once();
        $mock->shouldReceive('shouldReport')->andReturn(true)->once();

        app()->instance(ExceptionHandler::class, $mock);
    }

    public function testToReportSentryWithStandardException()
    {
        $this->spySentryProviderCaptureException();

        // test case exception
        $exception = new \Exception('report to sentry');

        $this->mockLogger($exception);

        app(ExceptionHandler::class)->report($exception);
    }

    public function testToReportSentryWithSentryScopedException()
    {
        $exception = \Mockery::mock(ExceptionAbstract::class);
        $exception->shouldReceive('report')->once();

        app(ExceptionHandler::class)->report($exception);
    }

    /**
     * @param $exception
     * @return void
     */
    private function mockLogger($exception): void
    {
        $mock = \Mockery::mock(LoggerInterface::class);
        $mock->expects()->log(\Mockery::any(), $exception->getMessage(), \Mockery::any())->once();
        // we force the logger in app to allows the main Exception Handler to log exceptions
        app()->instance(LoggerInterface::class, $mock);
    }

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|HubInterface
     */
    private function spySentryProviderCaptureException()
    {
        $mock = \Mockery::spy('Sentry\State\HubInterface[captureException]');
        $mock->shouldReceive('captureException')->once();

//        app()->instance(HubInterface::class, $mock);

        $loader = AliasLoader::getInstance();
        $loader->alias('sentry', HubInterface::class);

        app()->singleton('sentry', function () use ($mock) {
            return $mock;
        });

        return $mock;
    }
}
