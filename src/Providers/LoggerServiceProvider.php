<?php

namespace LogFormatter\Providers;

use Illuminate\Support\ServiceProvider;
use LogFormatter\Middleware\LogRequestResponse;
use Monolog\Formatter\FormatterInterface;
use LogFormatter\Formatters\JsonFormatter;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FormatterInterface::class, JsonFormatter::class);
    }

    public function boot(): void
    {
        $this->app['router']->pushMiddlewareToGroup('api', LogRequestResponse::class);
        $logger = $this->app['log']->getLogger();

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
