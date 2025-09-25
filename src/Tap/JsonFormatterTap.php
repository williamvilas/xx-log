<?php

namespace LogFormatter\Tap;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger as MonologLogger;
use LogFormatter\Contracts\FormatterInterface;

class JsonFormatterTap
{
    protected FormatterInterface $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function __invoke(IlluminateLogger $logger): void
    {
        /** @var MonologLogger $monolog */
        $monolog = $logger->getLogger();

        foreach ($monolog->getHandlers() as $handler) {
            $handler->setFormatter($this->formatter);
        }
    }
}
