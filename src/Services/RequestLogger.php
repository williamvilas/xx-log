<?php

namespace LogFormatter\Services;

use Illuminate\Support\Facades\Log;
use LogFormatter\Contracts\FormatterInterface;
use LogFormatter\Contracts\LoggerInterface;

class RequestLogger implements LoggerInterface
{
    public function __construct(
        private FormatterInterface $formatter
    ) {}

    public function log(string $level, array $data): void
    {
        $message = $this->formatter->format($data);
        Log::log($level, $message);
    }
}