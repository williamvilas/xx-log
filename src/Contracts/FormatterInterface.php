<?php

namespace LogFormatter\Contracts;

use Monolog\LogRecord;

interface FormatterInterface
{
    public function format(LogRecord|array $record);

    public function formatBatch(array $records);
}