<?php

namespace LogFormatter\Formatters;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class JsonFormatter implements FormatterInterface
{
    public function format($record): string
    {
        $data = $this->normalizeRecord($record);

        $formatter = [
            'application' => env('APP_NAME', 'laravel'),
            'environment' => env('APP_ENV', 'local'),
            'level'       => $data['level_name'],
            'message'     => $data['message'],
            'context'     => $data['context'],
            'datetime'    => $data['datetime'],
        ];

        return json_encode($formatter, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function formatBatch(array $records): array
    {
        return array_map([$this, 'format'], $records);
    }

    private function normalizeRecord(LogRecord|array $record): array
    {
        if ($record instanceof LogRecord) {
            return [
                'level_name' => $record->level->getName(),
                'message'    => $record->message,
                'context'    => $record->context,
                'datetime'   => $record->datetime->format('c'),
            ];
        }

        $datetime = isset($record['datetime']) && $record['datetime'] instanceof \DateTimeInterface
            ? $record['datetime']->format('c')
            : date('c');

        return [
            'level_name' => $record['level_name'] ?? 'UNKNOWN',
            'message'    => $record['message'] ?? '',
            'context'    => $record['context'] ?? [],
            'datetime'   => $datetime,
        ];
    }
}
