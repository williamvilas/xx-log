<?php

namespace LogFormatter\Tap;

use Monolog\Logger;
use LogFormatter\Contracts\FormatterInterface;

class JsonFormatterTap
{
    protected FormatterInterface $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($this->formatter);
        }
    }
}