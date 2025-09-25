<?php

namespace LogFormatter\Formatters;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class JsonFormatter implements FormatterInterface
{
    public function format(LogRecord $record)
    {
        return json_encode([
                'application'   => env('APP_NAME', 'laravel'),
                'environment'   => env('APP_ENV', 'local'),
                'level'         => $record['level_name'],
                'message'       => $record['message'],
                'context'       => $record['context'],
                'datetime'      => $record['datetime']->format('c'),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function formatBatch(array $records)
    {
        return array_map([$this, 'format'], $records);
    }
}