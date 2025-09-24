<?php

namespace LogFormatter\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
        // 1️⃣ Registrar middleware
        $this->app['router']->pushMiddlewareToGroup('api', LogRequestResponse::class);

        // 2️⃣ Substituir o log default do Laravel
        Log::extend('logformatter', function ($app, $config) {
            $monolog = new Logger('logformatter');
            $handler = new StreamHandler(storage_path('logs/laravel.log'));

            // Pega a implementação do formatter da SDK
            $formatter = $app->make(FormatterInterface::class);
            $handler->setFormatter($formatter);

            $monolog->pushHandler($handler);
            return $monolog;
        });

        // 3️⃣ Define este canal como default em tempo de execução
        $this->app['config']->set('logging.default', 'logformatter');
    }
}
