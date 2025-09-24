<?php

namespace LogFormatter\Formatters;

use LogFormatter\Contracts\FormatterInterface;

class JsonFormatter implements FormatterInterface
{
    public function format(array $data): string
    {
        return json_encode([
            'application' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp'   => now()->toISOString(),
            'data'        => $data,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}