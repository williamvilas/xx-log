<?php

namespace LogFormatter\Providers;

use Illuminate\Support\ServiceProvider;
use LogFormatter\Contracts\FormatterInterface;
use LogFormatter\Contracts\LoggerInterface;
use LogFormatter\Formatters\JsonFormatter;
use LogFormatter\Middleware\LogRequestResponse;
use LogFormatter\Services\RequestLogger;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Inversão de dependência
        $this->app->bind(FormatterInterface::class, JsonFormatter::class);
        $this->app->bind(LoggerInterface::class, RequestLogger::class);
    }

    public function boot(): void
    {
        $this->app['router']->pushMiddlewareToGroup('api', LogRequestResponse::class);
    }
}