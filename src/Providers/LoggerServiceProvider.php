<?php

namespace LogFormatter\Providers;

use Illuminate\Support\ServiceProvider;
use LogFormatter\Contracts\FormatterInterface;
use LogFormatter\Contracts\LoggerInterface;
use LogFormatter\Formatters\JsonFormatter;
use LogFormatter\Services\RequestLogger;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FormatterInterface::class, JsonFormatter::class);
        $this->app->bind(LoggerInterface::class, RequestLogger::class);
    }

    public function boot(): void
    {
        $logger = $this->app['log']->getLogger();

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
