<?php

namespace LogFormatter\Contracts;

interface FormatterInterface
{
    public function format(array $record);

    public function formatBatch(array $records);
}